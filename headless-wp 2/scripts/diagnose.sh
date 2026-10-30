#!/usr/bin/env bash
# Starter check: all required plugins active, no PHP warnings, schema exposes our types.
set -uo pipefail

cd "$(dirname "$0")/../wordpress"
REQUIRED="woocommerce wp-graphql wp-graphql-woocommerce headless-commerce-engine"
LOG="wp-content/debug.log"
fail=0

echo "== Plugin status =="
wp plugin list --fields=name,status,version

for p in $REQUIRED; do
  status=$(wp plugin get "$p" --field=status 2>/dev/null || echo "missing")
  if [ "$status" != "active" ]; then
    echo "FAIL: $p is $status"
    fail=1
  fi
done

echo
echo "== PHP warnings / notices =="
: > "$LOG"   # start from a clean log, then exercise WordPress + GraphQL
out=$(wp eval '
  do_action( "wp_loaded" );
  $r = graphql( [ "query" => "{ __type(name: \"HceProductData\") { name } hceBrand: __type(name: \"HceBrand\") { name } }" ] );
  echo wp_json_encode( $r ), "\n";
' 2>&1)
echo "$out"

if echo "$out" | grep -qiE 'PHP (Warning|Notice|Deprecated|Fatal)|Fatal error|Warning:|Deprecated:'; then
  echo "FAIL: PHP output contains warnings"; fail=1
fi
if [ -s "$LOG" ] && grep -qiE 'PHP (Warning|Notice|Deprecated|Fatal)' "$LOG"; then
  echo "FAIL: debug.log contains PHP warnings:"; cat "$LOG"; fail=1
fi
if ! echo "$out" | grep -q '"HceProductData"'; then
  echo "FAIL: HceProductData missing from GraphQL schema"; fail=1
fi

echo
echo "== Core =="
wp core version
wp core verify-checksums 2>&1 | tail -n 1

echo
[ "$fail" -eq 0 ] && echo "ALL CHECKS PASSED" || { echo "CHECKS FAILED"; exit 1; }
