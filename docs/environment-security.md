# Environment variables and browser exposure

## Findings

- The local `.env` is Git-ignored and is not present in the repository's reachable commit history. `.env.example` is the only environment file found in history.
- A scan of all 3 reachable commits found no credential assignments or common GitHub, Slack, AWS, or private-key token patterns. No committed credential was identified for rotation.
- The local `.env` contains a generated `APP_KEY`; it is not committed. Keep it server-side and do not share it. Other secret-shaped local settings checked were empty/default.
- No `import.meta.env` reference or `@vite` directive is present in the current application views/source. Laravel reads its environment values on the server. The Blade CSRF meta tag contains a per-session CSRF token, not an environment variable or reusable application secret.
- Vite is restricted to `VITE_PUBLIC_*`. `VITE_PUBLIC_APP_NAME` is a nonsecret display name and is not currently read by frontend code. Do not put credentials or server settings under that prefix.

## Variables defined in `.env.example` and the local `.env`

The local `.env` and template define the same 44 setting names; the app-name field is `VITE_PUBLIC_APP_NAME` in both.

`APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL`, `APP_LOCALE`, `APP_FALLBACK_LOCALE`, `APP_FAKER_LOCALE`, `APP_MAINTENANCE_DRIVER`, `PHP_CLI_SERVER_WORKERS`, `BCRYPT_ROUNDS`, `LOG_CHANNEL`, `LOG_STACK`, `LOG_DEPRECATIONS_CHANNEL`, `LOG_LEVEL`, `DB_CONNECTION`, `SESSION_DRIVER`, `SESSION_LIFETIME`, `SESSION_ENCRYPT`, `SESSION_PATH`, `SESSION_DOMAIN`, `BROADCAST_CONNECTION`, `FILESYSTEM_DISK`, `QUEUE_CONNECTION`, `CACHE_STORE`, `MEMCACHED_HOST`, `REDIS_CLIENT`, `REDIS_HOST`, `REDIS_PASSWORD`, `REDIS_PORT`, `MAIL_MAILER`, `MAIL_SCHEME`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, `AWS_USE_PATH_STYLE_ENDPOINT`, `VITE_PUBLIC_APP_NAME`.

All except `VITE_PUBLIC_APP_NAME` are read by Laravel/PHP server configuration or local tooling. None is currently read in browser JavaScript. The Vite public name is explicitly eligible for frontend code, but is not used by the current frontend.

## Additional optional Laravel configuration names

These names are read by the shipped Laravel configuration files if configured. They are not all defined in `.env.example` or the local `.env`.

- `config/app.php`: `APP_DEBUG`, `APP_ENV`, `APP_FAKER_LOCALE`, `APP_FALLBACK_LOCALE`, `APP_KEY`, `APP_LOCALE`, `APP_MAINTENANCE_DRIVER`, `APP_MAINTENANCE_STORE`, `APP_NAME`, `APP_PREVIOUS_KEYS`, `APP_URL`.
- `config/auth.php`: `AUTH_GUARD`, `AUTH_MODEL`, `AUTH_PASSWORD_BROKER`, `AUTH_PASSWORD_RESET_TOKEN_TABLE`, `AUTH_PASSWORD_TIMEOUT`.
- `config/cache.php`: `APP_NAME`, `AWS_ACCESS_KEY_ID`, `AWS_DEFAULT_REGION`, `AWS_SECRET_ACCESS_KEY`, `CACHE_PREFIX`, `CACHE_STORE`, `DB_CACHE_CONNECTION`, `DB_CACHE_LOCK_CONNECTION`, `DB_CACHE_LOCK_TABLE`, `DB_CACHE_TABLE`, `DYNAMODB_CACHE_TABLE`, `DYNAMODB_ENDPOINT`, `MEMCACHED_HOST`, `MEMCACHED_PASSWORD`, `MEMCACHED_PERSISTENT_ID`, `MEMCACHED_PORT`, `MEMCACHED_USERNAME`, `REDIS_CACHE_CONNECTION`, `REDIS_CACHE_LOCK_CONNECTION`.
- `config/database.php`: `APP_NAME`, `DB_CHARSET`, `DB_COLLATION`, `DB_CONNECTION`, `DB_DATABASE`, `DB_ENCRYPT`, `DB_FOREIGN_KEYS`, `DB_HOST`, `DB_PASSWORD`, `DB_PORT`, `DB_SOCKET`, `DB_TRUST_SERVER_CERTIFICATE`, `DB_URL`, `DB_USERNAME`, `MYSQL_ATTR_SSL_CA`, `REDIS_CACHE_DB`, `REDIS_CLIENT`, `REDIS_CLUSTER`, `REDIS_DB`, `REDIS_HOST`, `REDIS_PASSWORD`, `REDIS_PERSISTENT`, `REDIS_PORT`, `REDIS_PREFIX`, `REDIS_URL`, `REDIS_USERNAME`.
- `config/filesystems.php`: `APP_URL`, `AWS_ACCESS_KEY_ID`, `AWS_BUCKET`, `AWS_DEFAULT_REGION`, `AWS_ENDPOINT`, `AWS_SECRET_ACCESS_KEY`, `AWS_URL`, `AWS_USE_PATH_STYLE_ENDPOINT`, `FILESYSTEM_DISK`.
- `config/logging.php`: `LOG_CHANNEL`, `LOG_DAILY_DAYS`, `LOG_DEPRECATIONS_CHANNEL`, `LOG_DEPRECATIONS_TRACE`, `LOG_LEVEL`, `LOG_PAPERTRAIL_HANDLER`, `LOG_SLACK_EMOJI`, `LOG_SLACK_USERNAME`, `LOG_SLACK_WEBHOOK_URL`, `LOG_STACK`, `LOG_STDERR_FORMATTER`, `LOG_SYSLOG_FACILITY`, `PAPERTRAIL_PORT`, `PAPERTRAIL_URL`.
- `config/mail.php`: `APP_URL`, `MAIL_EHLO_DOMAIN`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`, `MAIL_HOST`, `MAIL_LOG_CHANNEL`, `MAIL_MAILER`, `MAIL_PASSWORD`, `MAIL_PORT`, `MAIL_SCHEME`, `MAIL_SENDMAIL_PATH`, `MAIL_URL`, `MAIL_USERNAME`, `POSTMARK_MESSAGE_STREAM_ID`.
- `config/queue.php`: `AWS_ACCESS_KEY_ID`, `AWS_DEFAULT_REGION`, `AWS_SECRET_ACCESS_KEY`, `BEANSTALKD_QUEUE`, `BEANSTALKD_QUEUE_HOST`, `BEANSTALKD_QUEUE_RETRY_AFTER`, `DB_CONNECTION`, `DB_QUEUE`, `DB_QUEUE_CONNECTION`, `DB_QUEUE_RETRY_AFTER`, `DB_QUEUE_TABLE`, `QUEUE_CONNECTION`, `QUEUE_FAILED_DRIVER`, `REDIS_QUEUE`, `REDIS_QUEUE_CONNECTION`, `REDIS_QUEUE_RETRY_AFTER`, `SQS_PREFIX`, `SQS_QUEUE`, `SQS_SUFFIX`.
- `config/services.php`: `AWS_ACCESS_KEY_ID`, `AWS_DEFAULT_REGION`, `AWS_SECRET_ACCESS_KEY`, `POSTMARK_TOKEN`, `RESEND_KEY`, `SLACK_BOT_USER_DEFAULT_CHANNEL`, `SLACK_BOT_USER_OAUTH_TOKEN`.
- `config/session.php`: `APP_NAME`, `SESSION_CONNECTION`, `SESSION_COOKIE`, `SESSION_DOMAIN`, `SESSION_DRIVER`, `SESSION_ENCRYPT`, `SESSION_EXPIRE_ON_CLOSE`, `SESSION_HTTP_ONLY`, `SESSION_LIFETIME`, `SESSION_PARTITIONED_COOKIE`, `SESSION_PATH`, `SESSION_SAME_SITE`, `SESSION_SECURE_COOKIE`, `SESSION_STORE`, `SESSION_TABLE`.

All optional Laravel configuration values above remain server-side. Secrets such as `APP_KEY`, database passwords, mail tokens, AWS credentials, Slack tokens, Redis passwords, and log webhooks must be read only by backend code and sent only from authenticated/authorized server routes when needed. Never echo them into Blade, public assets, API responses, or `VITE_PUBLIC_*` variables.
