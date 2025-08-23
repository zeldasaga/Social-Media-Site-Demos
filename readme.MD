# Social Media Demo Project Shared Info
In order to learn different programming languages, frameworks, and concepts I decided to make a common complex project as a base.  Having a common and mostly well defined starting point allows me to hop right into a given language or framework without the overhead of thinking up a new project, designing requirements, or aimlessly following tutorials without a clear end goal in sight. Plus, a somewhat real-world project can help me identify strengths, weaknesses, and workarounds for certain architectural choices.

The goal is to implement this demo project in multiple languages and/or frameworks, so each of the folders in this directory are the starting point for the same project implemented in a different way.  For example to see the project implemented in Java Spring Boot, find the folder `social-media-site-java` and click into it to see the top level working directly for a Java implementation.

## Common Architectural Decisions (unless otherwise noted)
- The database used in each project is MySQL
- The frontend framework used is ReactJS

## Core Functional Requirements
### 1. Authentication & Accounts
- Email/password + OAuth (Google/GitHub) with email verification and password reset.
- Sessions + refresh tokens; device/session management.
- Profile setup: display name, handle (unique), bio, avatar, banner.

### 2. User Profiles & Social Graph
- Follow/unfollow; counts (followers/following).
- Privacy: public vs. private accounts (follow requests/approvals).
- Block/mute users.

### 3. Posts
- Text (up to N chars), hashtags, @mentions.
- Images/video upload (thumbnailing/transcoding), alt text for images.
- Visibility: public, followers-only (if private account).
- Edit window (e.g., 5 minutes) + immutable edit history.

### 4. Interactions
- Likes, comments (1 level deep for MVP), delete own content.
- Share/repost (optional for MVP).
- Rate limits (posts, comments, likes) to prevent spam.


### 5. Feed
- Home feed: followed users’ posts in reverse-chron with simple ranking.
- User profile feed.


### 5. Pagination (cursor-based).
- Search & Discovery
- Search users by handle/name.
- Hashtag pages.
- (Stretch) Full-text search on posts.


### 6. Notifications
- In-app notifications for likes, comments, follows, mentions, follow requests.
- Read/unread state, notification settings.


### 7. Direct Messages (Stretch)
- 1:1 messaging with typing indicators and read receipts.


### 8. Moderation & Safety
- Report content or users with reason categories.
- Admin review queue; actions: warn, hide, suspend.
- Profanity/NSFW filters (configurable).
- Abuse prevention: CAPTCHA on signup/rare actions, IP throttling.


### 9. Admin Panel
- User search, content review, metrics dashboard (DAU/MAU, posts/day).
- Feature flag toggles.

## API Endpoint Definitions
### Authorization
POST /auth/signup
POST /auth/login
POST /auth/refresh
POST /auth/logout


### User Actions
GET /users/:handle
PATCH /users/me
POST /users/:handle/follow
DELETE /users/:handle/follow


### Safety/Moderation Actions
POST /blocks/:handle
DELETE /blocks/:handle
POST /reports


### Content Actions
POST /posts
GET /posts/:id
DELETE /posts/:id
GET /feed/home?cursor=...
POST /posts/:id/like, DELETE /posts/:id/like
POST /posts/:id/comments
GET /posts/:id/comments


### Search
GET /search/users?q=
GET /search/hashtags/:tag


### Notifications
GET /notifications
PATCH /notifications/:id (mark read)

## Testing Criteria
- Signup: Given valid email/password, when user confirms email, account is created and session issued; rate limited to 5/min/IP.

- Post Create: Given text <= N chars and optional media, post appears in author profile and followers’ feeds within 1s (cache eventual).

- Follow Private User: Follow request enters “pending”; target sees notification; upon approval, requester sees private posts.

## Bonus Requirements
- Performance: P95 API < 200 ms for hot paths; image uploads < 5 MB; video < 100 MB (configurable).

- Availability: 99.5% for MVP.

- Scalability: Horizontally scalable stateless API; CDN for media.

- Security: OWASP Top 10, TLS everywhere, hashed passwords (Argon2/bcrypt), CSRF protection, input validation.

- Privacy & Compliance: Data retention policy, account export/delete; cookie consent; age gate if needed.

- Accessibility: WCAG 2.2 AA; keyboard nav; color-contrast; alt text required for images.

- Internationalization: i18n framework; timezones; RTL support.

- Observability: Structured logs, tracing, metrics (Prometheus/OpenTelemetry), alerting.


# Database ERD (MySQL 8.0)

> ERD tailored for MySQL 8 with MySQL-friendly table names, data types, and notes on indexes/constraints.
> Notation style pulled loosley from https://github.com/BurntSushi/erd
> - `?` means 0 or 1
> - `1` means exactly 1
> - `*` means 0 or more
> - `+` means 1 or more

```mermaid
erDiagram
    USERS ||--|| PROFILES : "has"
    USERS ||--o{ POSTS : "writes"
    USERS ||--o{ COMMENTS : "writes"
    POSTS ||--o{ COMMENTS : "has"

    USERS ||--o{ POST_LIKES : "creates"
    POSTS ||--o{ POST_LIKES : "receives"

    USERS ||--o{ FOLLOWS : "follower"
    USERS ||--o{ FOLLOWS : "followee"

    USERS ||--o{ BLOCKS : "blocker"
    USERS ||--o{ BLOCKS : "blocked"

    USERS ||--o{ REPORTS : "files"
    REPORTS }o--|| USERS : "target_user (optional)"
    REPORTS }o--|| POSTS : "target_post (optional)"
    REPORTS }o--|| COMMENTS : "target_comment (optional)"

    USERS ||--o{ NOTIFICATIONS : "recipient"
    USERS }o--o{ NOTIFICATIONS : "actor (optional)"

    POSTS ||--o{ MEDIA_ASSETS : "has"

    POSTS }o--o{ POST_HASHTAGS : "tagged_with"
    HASHTAGS }o--o{ POST_HASHTAGS : "tags"

    %% ENTITIES (MySQL-leaning types)
    USERS {
        bigint unsigned id PK
        varchar(32) handle UK  "utf8mb4_0900_ai_ci"
        varchar(255) email UK   "utf8mb4_0900_ai_ci"
        varchar(255) password_hash
        tinyint(1) is_private  "BOOLEAN alias"
        enum('active','suspended','deleted') status
        datetime created_at
        datetime updated_at
    }

    PROFILES {
        bigint unsigned user_id PK, FK
        varchar(80) display_name
        text bio
        varchar(512) avatar_url
        varchar(512) banner_url
        varchar(64) timezone
        varchar(16) locale
        datetime updated_at
    }

    POSTS {
        bigint unsigned id PK
        bigint unsigned author_id FK
        text text
        enum('public','followers') post_privacy
        datetime created_at
        datetime updated_at
    }

    COMMENTS {
        bigint unsigned id PK
        bigint unsigned post_id FK
        bigint unsigned author_id FK
        text text
        datetime created_at
        datetime updated_at
    }

    POST_LIKES {
        bigint unsigned user_id PK, FK
        bigint unsigned post_id PK, FK
        datetime created_at
    }

    FOLLOWS {
        bigint unsigned follower_id PK, FK
        bigint unsigned followee_id PK, FK
        enum('pending','accepted','blocked') status
        datetime created_at
    }

    BLOCKS {
        bigint unsigned blocker_id PK, FK
        bigint unsigned blocked_id PK, FK
        datetime created_at
    }

    REPORTS {
        bigint unsigned id PK
        bigint unsigned reporter_id FK
        enum('user','post','comment') target_type
        bigint unsigned target_id  "FK varies by type"
        varchar(255) reason
        enum('open','reviewing','resolved','rejected') status
        datetime created_at
    }

    NOTIFICATIONS {
        bigint unsigned id PK
        bigint unsigned user_id FK  "recipient"
        enum('like','comment','follow','mention','follow_request','system') type
        bigint unsigned actor_id FK "nullable"
        enum('post','comment','user','system') object_type
        bigint unsigned object_id
        datetime read_at "nullable"
        datetime created_at
    }

    MEDIA_ASSETS {
        bigint unsigned id PK
        bigint unsigned post_id FK
        enum('image','video') media_type
        varchar(768) url
        int width
        int height
        decimal(8,3) duration "seconds, nullable"
        varchar(255) alt_text "required for accessibility"
        datetime created_at
    }

    HASHTAGS {
        bigint unsigned id PK
        varchar(255) tag UK  "utf8mb4_0900_ai_ci"
        datetime created_at
    }

    POST_HASHTAGS {
        bigint unsigned post_id PK, FK
        bigint unsigned hashtag_id PK, FK
    }
```

---

## MySQL-Specific Notes & Assumptions

* **Engine & Charset:** Use `InnoDB` with `utf8mb4` and collation `utf8mb4_0900_ai_ci` (case-insensitive). If you want **case-sensitive unique** handles/hashtags, use `_cs` collation or `BINARY` columns for those fields.
* **Booleans:** `BOOLEAN` in MySQL is an alias of `TINYINT(1)`; modeled here as `tinyint(1)`.
* **Timestamps:** Consider `created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP` and `updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` where desired. `DATETIME` is shown for portability.
* **Reserved Words:** Table renamed to `POST_LIKES` to avoid `LIKE`. Plural table names used to sidestep edge cases like `USER`/`COMMENT`.
* **Composite Keys:** Junctions (`POST_LIKES`, `FOLLOWS`, `POST_HASHTAGS`, `BLOCKS`) use composite primary keys; add covering secondary indexes for lookup directions.
* **FK Actions (suggested):**

  * `PROFILES.user_id` → `USERS.id` **ON DELETE CASCADE**.
  * `POSTS.author_id` → `USERS.id` **ON DELETE RESTRICT** (or CASCADE if you hard-delete users).
  * `COMMENTS.post_id` → `POSTS.id` **ON DELETE CASCADE**; `COMMENTS.author_id` → `USERS.id` RESTRICT.
  * `POST_LIKES.post_id`/`user_id` → CASCADE on post/user delete (or clean up via jobs if soft-deleting).
  * `FOLLOWS`/`BLOCKS` → CASCADE on user deletes.
  * `MEDIA_ASSETS.post_id` → CASCADE.
  * `NOTIFICATIONS.user_id` (recipient) → CASCADE; `actor_id` → SET NULL.
  * `REPORTS.target_*` is polymorphic; enforce via application logic and CHECK-like constraints (MySQL 8.0 supports `CHECK`, but they are not validated prior to 8.0.16). Alternatively, split into `REPORTS_USER`, `REPORTS_POST`, `REPORTS_COMMENT` tables.

## Indexing Cheatsheet (MySQL 8.0)

* `USERS(handle)`, `USERS(email)` **UNIQUE**.
* `POSTS(author_id, created_at)` for profile feeds.
* `COMMENTS(post_id, created_at)`.
* `POST_LIKES(post_id)` and `POST_LIKES(user_id)`.
* `FOLLOWS(followee_id)` and `FOLLOWS(follower_id)` for social graph queries.
* `POST_HASHTAGS(hashtag_id)` and `POST_HASHTAGS(post_id)`.
* **Search:** `FULLTEXT` on `POSTS(text)` and optionally `HASHTAGS(tag)` for hashtag search (InnoDB supports FULLTEXT in MySQL ≥5.6; rankings improve in 8.0).

## Next Steps

* I can generate **MySQL 8.0 DDL** (CREATE TABLEs with engines, charsets, collations, indexes, and FKs) directly from this ERD.
* If you plan on **soft-deletes**, add `deleted_at` columns and replace CASCADEs with application-level cleanup.
* Add **rate limit** and **audit** tables if needed for moderation and abuse mitigation.
