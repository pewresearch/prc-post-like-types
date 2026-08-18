# PRC "Post-Like" Types

> Canonical docs: [docs/plugins/prc-post-like-types/](../../docs/plugins/prc-post-like-types/)

Registers a set of independent, `post`-mirroring custom post types for PRC Platform — `decoded`, `press-release`, and `short-read` — each with a shared date-based permalink structure (`/{type}/YYYY/MM/DD/{slug}/`) and full platform feature support.

## What it does

- Registers three post types via a central `Registry` class: `decoded`, `press-release`, and `short-read`
- Applies a consistent date-based permalink structure (`/{rewrite-slug}/YYYY/MM/DD/{post-name}/`) to all registered types via `post_type_link` and custom rewrite rules
- Opts all registered types into the `prc_platform_post_publish_pipeline` post-publish pipeline
- Types that declare `pub_listing` support (`decoded`, `short-read`) get `prc-publication-listing` support and are included in publication listings and the main RSS feed via `prc-publication-listing`
- Auto-enforces a matching `formats` taxonomy term on every incremental save (e.g. a `decoded` post always gets the `decoded` format term)
- Attaches `notes` editor support to each registered post type
- Loads a WP-CLI utility: `wp prc templates bulk-update` (bulk `_wp_page_template` changes across any post type; defaults to dry-run)

## Registered post types

| Post type       | Rewrite slug    | `pub_listing` | Taxonomies (additional)                                       |
| --------------- | --------------- | ------------- | ------------------------------------------------------------- |
| `decoded`       | `decoded`       | yes           | `decoded-category`, `bylines`, `category`, `_post_visibility` |
| `press-release` | `press-release` | no            | `collection`                                                 |
| `short-read`    | `short-reads`   | yes           | `datasets`, `collection`, `bylines`, `_post_visibility`      |

All types share these base taxonomies: `category`, `collection`, `formats`, `languages`, `research-teams`.

All types support: `title`, `editor`, `excerpt`, `author`, `thumbnail`, `revisions`, `prc-revisions`, `custom-fields`, `comments`, `prc-schema-seo`, `prc-social`, `prc-bylines`, `prc-art-direction`, `prc-related-posts`, `prc-datasets`, `prc-collections`, `prc-sitemap`, `prc-markdown-for-agents`. Types with `pub_listing` additionally support `prc-publication-listing`.

## Permalink structure

WordPress's default CPT permalink for `decoded` would be `/decoded/{slug}/`. This plugin rewrites it to `/decoded/YYYY/MM/DD/{slug}/` by:

1. Calling `add_rewrite_rule()` directly on the `init` hook
2. Filtering `post_type_link` at priority 30 to inject the date path into published posts

Unpublished posts fall through to the default URL and are unaffected.

## Key files

| File                                                 | Purpose                                                                                                     |
| ---------------------------------------------------- | ----------------------------------------------------------------------------------------------------------- |
| `prc-post-like-types.php`                            | Plugin entry point; defines constants and boots `Plugin`                                                    |
| `includes/class-plugin.php`                          | Wires dependencies, instantiates `Registry` with all three post type definitions                            |
| `includes/class-registry.php`                        | Core logic: post type construction, rewrite rules, permalink filtering, format enforcement, pipeline opt-in |
| `includes/class-cli.php`                             | WP-CLI: `prc templates bulk-update` for `_wp_page_template` migrations at scale (VIP bulk patterns)         |
| `includes/class-loader.php`                          | Collects and registers all `add_action` / `add_filter` calls with WordPress                                 |

## Filters / hooks

| Hook                                            | Direction            | Description                                                                                            |
| ----------------------------------------------- | -------------------- | ------------------------------------------------------------------------------------------------------ |
| `init`                                          | Action               | Registers date-based and attachment rewrite rules for each registered post type via `add_rewrite_rule` |
| `post_type_link`                                | Filter (priority 30) | Rewrites the permalink of published post-like posts to include `YYYY/MM/DD`                            |
| `init`                                          | Action               | Calls `register_post_type()` for each entry in the registry                                            |
| `prc_platform_post_publish_pipeline_post_types` | Filter               | Appends all registered slugs so they enter the post-publish pipeline                                   |
| `prc_platform_on_incremental_save`              | Action               | Enforces a matching `formats` term on every save of a post-like type                                   |

## Adding a new post-like type

Call `Registry::register()` in `class-plugin.php` before `$this->loader->run()`:

```php
$this->registry->register(
    array(
        'slug'        => 'my-type',
        'singular'    => 'My Type',
        'plural'      => 'My Types',
        'description' => 'Description shown in WP admin.',
        'pub_listing' => false, // true to include in main feed and publication listings
    ),
    array(
        'rewrite' => array(
            'slug' => 'my-type', // URL prefix
        ),
    ),
    array( 'bylines', 'collection' ) // additional taxonomies beyond the defaults
);
```

After adding a type, flush rewrite rules (`wp rewrite flush`).

## Dependencies

- `prc-platform-core` (declared via `Requires Plugins` header)
- Platform filters consumed: `prc_platform_post_publish_pipeline_post_types`, `prc_platform_on_incremental_save`

## Notes

- The `formats` term auto-enforcement on `prc_platform_on_incremental_save` will create the term if it does not already exist in the `formats` taxonomy. This is a side effect to be aware of in fresh environments.
- The `_post_visibility` taxonomy is automatically added for any type registered with `pub_listing => true`. It is managed by the platform and should not be added manually.
- All four registered types are `show_in_rest => true` and fully accessible via the REST API under their respective post type routes.
