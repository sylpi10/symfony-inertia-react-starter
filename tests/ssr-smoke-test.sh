#!/bin/sh
# Checks that the home page is really rendered by the Node SSR server, head tags included.
# Needs the bundles: run `npm run build` first (`make test-ssr` does it).
set -eu

cd "$(dirname "$0")/.."

PORT=8123
export APP_ENV=dev
export INERTIA_SSR=1
# The SSR switch is read when the container is compiled: use a throwaway cache,
# so your regular dev cache (compiled with SSR off) is left untouched.
APP_CACHE_DIR="$(mktemp -d)"
export APP_CACHE_DIR

node bootstrap/ssr/ssr.js >/dev/null 2>&1 &
SSR_PID=$!
php -S "127.0.0.1:$PORT" -t public tests/ssr-smoke-router.php >/dev/null 2>&1 &
PHP_PID=$!
trap 'kill "$SSR_PID" "$PHP_PID" 2>/dev/null; rm -rf "$APP_CACHE_DIR"' EXIT

# Wait for both servers
for _ in $(seq 1 20); do
    if php bin/console inertia:check-ssr >/dev/null 2>&1 && curl -sf -o /dev/null "http://127.0.0.1:$PORT/robots.txt"; then
        break
    fi
    sleep 0.5
done

HTML="$(curl -sf "http://127.0.0.1:$PORT/")"

fail() {
    echo "FAIL: $1" >&2
    exit 1
}

# look at <head>: in dev, the profiler toolbar adds its own markup to the body
HEAD="$(echo "$HTML" | sed -n '/<head>/,/<\/head>/p')"

echo "$HTML" | grep -q 'class="main-nav"' || fail "the page body is not server-rendered (is the SSR server up?)"
[ "$(echo "$HEAD" | grep -o '<title' | wc -l)" -eq 1 ] || fail "expected exactly one <title> in <head>"
echo "$HEAD" | grep -q '<title[^>]*>Home · ' || fail "the <title> is not the page title"
echo "$HEAD" | grep -q '<meta[^>]*name="description"' || fail "no meta description"
echo "$HEAD" | grep -q '<link[^>]*rel="canonical"' || fail "no canonical link"

echo "OK: the home page is server-rendered, with its title, description and canonical link."
