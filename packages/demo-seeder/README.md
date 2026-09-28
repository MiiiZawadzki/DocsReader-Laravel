# docsreader/demo-seeder

Seeds a demo company (staff, documents with real PDFs, reading history) for testing and demos.

    php artisan demo:seed

Rebuilds from scratch on each run. Only touches accounts on the
`demo.docsreader.test` domain and the documents they own, and refuses to run when
`APP_ENV=production` unless `--force` is passed.

## Removing it

    composer remove docsreader/demo-seeder
    rm -rf packages/demo-seeder

Nothing else to unpick: the service provider is auto-discovered, so no
application file references this package. Leave `packages/.gitkeep` in place --
the Dockerfile's `COPY packages ./packages` needs the directory to exist.
