# Headless WP + WooCommerce (Codespaces)

1. Open this repo in a GitHub Codespace (setup runs automatically).
2. Start the site: `cd wordpress && wp server --host=0.0.0.0 --port=8080`
3. Verify: `bash scripts/diagnose.sh`

GraphQL endpoint: `/graphql`. Custom plugin: `plugins/headless-commerce-engine`.
