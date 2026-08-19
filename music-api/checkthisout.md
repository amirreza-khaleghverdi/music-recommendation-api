# Music streaming API — database schema

Metadata + behavior layer only: tracks, artists, albums, playlists, listen events. No audio storage or delivery.

## Design principles

- **3NF for catalog data.** No repeated artist/album info duplicated onto tracks. Every many-to-many relationship (artist collaborations, various-artist compilations, genre tagging) gets its own pivot table instead of a comma-separated column or a single "primary artist" foreign key that can't represent features/collabs.
- **Pivots carry meaning, not just IDs.** `track_artists` and `album_artists` include a `role` column so a feature, a producer credit, and the primary artist are all representable — this is what makes a real-world collab track modelable at all.
- **Behavioral data is separated from catalog data.** `listen_events`, `track_likes`, and `artist_follows` are append-heavy and grow independently of the catalog. Keeping them as their own tables (not columns on `tracks`) means catalog reads stay fast and small, and the behavioral tables can be indexed and eventually partitioned on their own terms.
- **`track_audio_features` is 1:1, not inline.** Splitting it out means you can bulk-recompute or backfill features (tempo, energy, danceability, valence, etc.) without touching core catalog rows — this table is what your content-based recommendation work will read from later.
- **Genres are self-referential** (`parent_genre_id`) to support subgenre hierarchies instead of a flat tag list.

## Why this shape serves the recommendation system

- **Collaborative filtering** needs a user–item interaction signal → that's `listen_events` (implicit, via `ms_played`) and `track_likes` (explicit).
- **Content-based filtering** needs item features independent of who listened → that's `track_audio_features`.
- **Graph-like structure for artists** (who collaborates with whom, genre relationships) falls naturally out of `track_artists` + `track_genres` — you can derive an artist-similarity graph from co-occurrence without needing a dedicated graph table on day one.
- **Playlist co-occurrence** (tracks that show up together in playlists) is a common recommendation signal and is available directly from `playlist_tracks` with no extra modeling.

## Full DDL (PostgreSQL)

A few Postgres-specific choices made throughout, worth knowing before you read the SQL:

- **`GENERATED ALWAYS AS IDENTITY`** instead of `AUTO_INCREMENT` — this is the modern SQL-standard way to do auto-incrementing PKs in Postgres (preferred over the older `SERIAL` pseudo-type).
- **No `UNSIGNED`** — Postgres doesn't have unsigned integer types at all, so every FK/PK column is just `BIGINT` or `INTEGER`.
- **`VARCHAR` + `CHECK` instead of `ENUM`** — this matches what Laravel's `$table->enum()` actually generates on a Postgres connection (it doesn't create a native Postgres enum type, it compiles to a check constraint), so the DDL below reflects what your migrations will really produce.
- **`TIMESTAMPTZ` instead of `TIMESTAMP`** — stores timezone-aware instants, the Postgres-recommended default for event/audit timestamps rather than the naive `TIMESTAMP` type.
- **`SMALLINT` instead of `TINYINT`** — Postgres has no `TINYINT`; `SMALLINT` (2 bytes) is the smallest integer type available.

```sql
-- ========================================
-- CATALOG: artists, albums, tracks, genres
-- ========================================

CREATE TABLE artists (
    id              BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name            VARCHAR(150) NOT NULL,
    bio             TEXT NULL,
    country_code    CHAR(2) NULL,
    formed_year     SMALLINT NULL,
    image_url       VARCHAR(500) NULL,
    external_ids    JSONB NULL,             -- e.g. {"spotify_id": "...", "musicbrainz_id": "..."}
    search_vector   tsvector GENERATED ALWAYS AS (to_tsvector('english', name)) STORED,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at      TIMESTAMPTZ NULL
);
CREATE INDEX idx_artists_country ON artists(country_code);
CREATE INDEX idx_artists_search ON artists USING GIN (search_vector);

CREATE TABLE albums (
    id              BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    title           VARCHAR(200) NOT NULL,
    album_type      VARCHAR(20) NOT NULL DEFAULT 'album'
                        CHECK (album_type IN ('album','single','ep','compilation')),
    release_date    DATE NULL,
    cover_image_url VARCHAR(500) NULL,
    search_vector   tsvector GENERATED ALWAYS AS (to_tsvector('english', title)) STORED,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at      TIMESTAMPTZ NULL
);
CREATE INDEX idx_albums_release_date ON albums(release_date);
CREATE INDEX idx_albums_search ON albums USING GIN (search_vector);

CREATE TABLE album_artists (
    album_id    BIGINT NOT NULL REFERENCES albums(id) ON DELETE CASCADE,
    artist_id   BIGINT NOT NULL REFERENCES artists(id) ON DELETE CASCADE,
    role        VARCHAR(20) NOT NULL DEFAULT 'primary'
                    CHECK (role IN ('primary','featured','various')),
    PRIMARY KEY (album_id, artist_id, role)
);
CREATE INDEX idx_album_artists_artist ON album_artists(artist_id);

CREATE TABLE tracks (
    id              BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    album_id        BIGINT NOT NULL REFERENCES albums(id) ON DELETE CASCADE,
    title           VARCHAR(200) NOT NULL,
    track_number    SMALLINT NULL,
    disc_number     SMALLINT NOT NULL DEFAULT 1,
    duration_ms     INTEGER NOT NULL,
    isrc            VARCHAR(15) NULL UNIQUE,
    explicit        BOOLEAN NOT NULL DEFAULT FALSE,
    external_ids    JSONB NULL,             -- e.g. {"spotify_id": "...", "musicbrainz_id": "..."}
    search_vector   tsvector GENERATED ALWAYS AS (to_tsvector('english', title)) STORED,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at      TIMESTAMPTZ NULL
);
CREATE INDEX idx_tracks_album ON tracks(album_id);
CREATE INDEX idx_tracks_search ON tracks USING GIN (search_vector);

CREATE TABLE track_artists (
    track_id    BIGINT NOT NULL REFERENCES tracks(id) ON DELETE CASCADE,
    artist_id   BIGINT NOT NULL REFERENCES artists(id) ON DELETE CASCADE,
    role        VARCHAR(20) NOT NULL DEFAULT 'primary'
                    CHECK (role IN ('primary','featured','producer','composer','remixer')),
    PRIMARY KEY (track_id, artist_id, role)
);
CREATE INDEX idx_track_artists_artist ON track_artists(artist_id);

CREATE TABLE genres (
    id                INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name              VARCHAR(100) NOT NULL UNIQUE,
    parent_genre_id   INTEGER NULL REFERENCES genres(id) ON DELETE SET NULL
);
CREATE INDEX idx_genres_parent ON genres(parent_genre_id);

CREATE TABLE track_genres (
    track_id    BIGINT NOT NULL REFERENCES tracks(id) ON DELETE CASCADE,
    genre_id    INTEGER NOT NULL REFERENCES genres(id) ON DELETE CASCADE,
    PRIMARY KEY (track_id, genre_id)
);
CREATE INDEX idx_track_genres_genre ON track_genres(genre_id);

-- ========================================
-- CONTENT FEATURES (feeds content-based recs)
-- ========================================

CREATE TABLE track_audio_features (
    track_id            BIGINT PRIMARY KEY REFERENCES tracks(id) ON DELETE CASCADE,
    tempo_bpm           DECIMAL(6,2) NULL CHECK (tempo_bpm > 0),
    energy              DECIMAL(4,3) NULL CHECK (energy BETWEEN 0 AND 1),
    danceability        DECIMAL(4,3) NULL CHECK (danceability BETWEEN 0 AND 1),
    valence             DECIMAL(4,3) NULL CHECK (valence BETWEEN 0 AND 1),   -- musical positivity
    acousticness        DECIMAL(4,3) NULL CHECK (acousticness BETWEEN 0 AND 1),
    instrumentalness    DECIMAL(4,3) NULL CHECK (instrumentalness BETWEEN 0 AND 1),
    speechiness         DECIMAL(4,3) NULL CHECK (speechiness BETWEEN 0 AND 1),
    liveness            DECIMAL(4,3) NULL CHECK (liveness BETWEEN 0 AND 1),
    loudness_db         DECIMAL(5,2) NULL,
    key_signature       SMALLINT NULL CHECK (key_signature BETWEEN 0 AND 11),   -- pitch class
    mode                SMALLINT NULL CHECK (mode IN (0, 1)),                   -- 0 = minor, 1 = major
    time_signature      SMALLINT NULL CHECK (time_signature BETWEEN 1 AND 12)
);

-- ========================================
-- USERS & SOCIAL GRAPH
-- ========================================

CREATE TABLE users (
    id              BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    username        VARCHAR(50) NOT NULL UNIQUE,
    email           VARCHAR(255) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    display_name    VARCHAR(100) NULL,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at      TIMESTAMPTZ NULL
);

CREATE TABLE artist_follows (
    user_id       BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    artist_id     BIGINT NOT NULL REFERENCES artists(id) ON DELETE CASCADE,
    followed_at   TIMESTAMPTZ NOT NULL DEFAULT now(),
    PRIMARY KEY (user_id, artist_id)
);
CREATE INDEX idx_artist_follows_artist ON artist_follows(artist_id);

CREATE TABLE track_likes (
    user_id     BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    track_id    BIGINT NOT NULL REFERENCES tracks(id) ON DELETE CASCADE,
    liked_at    TIMESTAMPTZ NOT NULL DEFAULT now(),
    PRIMARY KEY (user_id, track_id)
);
CREATE INDEX idx_track_likes_track ON track_likes(track_id);

-- ========================================
-- PLAYLISTS
-- ========================================

CREATE TABLE playlists (
    id              BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id         BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title           VARCHAR(150) NOT NULL,
    description     TEXT NULL,
    is_public       BOOLEAN NOT NULL DEFAULT TRUE,
    cover_image_url VARCHAR(500) NULL,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at      TIMESTAMPTZ NULL
);
CREATE INDEX idx_playlists_user ON playlists(user_id);

CREATE TABLE playlist_tracks (
    id                BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    playlist_id       BIGINT NOT NULL REFERENCES playlists(id) ON DELETE CASCADE,
    track_id          BIGINT NOT NULL REFERENCES tracks(id) ON DELETE CASCADE,
    position          INTEGER NOT NULL,
    added_at          TIMESTAMPTZ NOT NULL DEFAULT now()
);
CREATE INDEX idx_playlist_tracks_playlist_position ON playlist_tracks(playlist_id, position);
CREATE INDEX idx_playlist_tracks_track ON playlist_tracks(track_id);
-- Surrogate id (not a composite PK on playlist_id+track_id) because most
-- streaming apps allow the same track to appear twice in a playlist.

-- ========================================
-- BEHAVIORAL / TIME-SERIES (recommendation fuel)
-- ========================================

CREATE TABLE listen_events (
    id              BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id         BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    track_id        BIGINT NOT NULL REFERENCES tracks(id) ON DELETE CASCADE,
    played_at       TIMESTAMPTZ NOT NULL,
    ms_played       INTEGER NOT NULL,
    context_type    VARCHAR(20) NULL
                        CHECK (context_type IN ('playlist','album','artist','search','radio','recommendation')),
    context_id      BIGINT NULL,
    device_type     VARCHAR(30) NULL
);
CREATE INDEX idx_listen_events_user_time ON listen_events(user_id, played_at);
CREATE INDEX idx_listen_events_track_time ON listen_events(track_id, played_at);
-- Append-only. No UPDATE statements against this table, ever — that's what
-- keeps it fast to write and safe to eventually partition by played_at.
```

## The other Postgres features — and why they're not (yet) in the DDL

**`RETURNING`** isn't something you write into a schema — it's a clause on `INSERT`/`UPDATE`/`DELETE` statements. You don't need to do anything for this one: Eloquent already appends `RETURNING id` (or the full row, depending on version) automatically when you `INSERT` on a Postgres connection, so `Model::create()` gets the generated ID back in the same round trip. On MySQL, Laravel has to do a separate `LAST_INSERT_ID()` call to get the same thing. This is a real advantage of being on Postgres, but it's invisible — nothing to add here.

**`LATERAL` joins** are also a query-time tool, not a schema one — worth knowing about for later once you're writing the recommendation/discography endpoints. The classic use case is "top N per group," which this schema will need constantly (an artist's most-played track, a user's most recent N plays):

```sql
SELECT a.id AS artist_id, a.name, top.title, top.play_count
FROM artists a
CROSS JOIN LATERAL (
    SELECT tr.title, COUNT(*) AS play_count
    FROM tracks tr
    JOIN track_artists ta ON ta.track_id = tr.id AND ta.artist_id = a.id
    JOIN listen_events le ON le.track_id = tr.id
    GROUP BY tr.id, tr.title
    ORDER BY play_count DESC
    LIMIT 1
) AS top ON true;
```

MySQL only got a usable equivalent (window functions doing similar work) fairly recently and it's clunkier for this exact pattern — this is a spot where Postgres syntax is a genuine improvement, not just a difference.

**pgvector** — deliberately not added yet. Adding an empty `vector` column now, before you have embeddings to put in it, is schema clutter you'd have to explain in your README with no payoff. The right moment is month 3, when `track_audio_features` (or a new `track_embeddings` table) gets a `vector(N)` column sized to whatever model you're using, plus an HNSW index. Flagging it here so it doesn't get forgotten.

**PostGIS** — genuinely doesn't fit the current scope. Nothing in this schema is geospatial; `country_code` is a two-letter code, not a location. It'd only become relevant if you added a "concerts near you" or venue-mapping feature, which is outside "metadata and behavior layer" as you've scoped it. Not recommending you add it artificially just to show Postgres range — a schema with an unused extension is a worse signal than a schema that only reaches for what the domain needs.

## Indexing rationale, table by table

| Table | Index | Query it serves |
|---|---|---|
| `listen_events` | `(user_id, played_at)` | "What has this user played, most recent first" — their listening history |
| `listen_events` | `(track_id, played_at)` | Track popularity over a time window — feeds trending/popularity features |
| `playlist_tracks` | `(playlist_id, position)` | Fetching a playlist's tracks in order — the single most common read |
| `track_artists` / `album_artists` | index on `artist_id` | "Everything this artist appears on" — an artist's discography page |
| `tracks` | index on `album_id` | Loading an album's tracklist |
| `genres` | index on `parent_genre_id` | Walking subgenre hierarchies |

Foreign key columns are indexed on the "many" side deliberately — most engines auto-index the referencing column in a FK, but it's worth verifying with `EXPLAIN` rather than assuming, especially on the pivot tables where the primary key is composite and doesn't automatically cover lookups from the *second* column (e.g. `track_artists` PK covers `track_id → ...` lookups for free, but `artist_id → tracks` needs its own index, which is why `idx_track_artists_artist` exists).

## Two things to deliberately hit this week

1. **Transaction boundary:** creating a playlist and seeding it with initial tracks should be one transaction. Try it without one first, kill the request mid-insert, and look at the orphaned row — then wrap it and confirm you can't reproduce that state.
2. **N+1 trap:** write the "get playlist with all tracks, their artists, and album titles" endpoint the naive way first, turn on query logging, and count how many queries run for a 20-track playlist. That number is your baseline for when eager loading shows up in week 2.

## Scaling note (not needed yet, worth knowing)

`listen_events` will be the fastest-growing table by a wide margin. At any real scale it eventually gets partitioned by `played_at` (monthly range partitions) and older partitions get archived or rolled up into aggregate tables rather than queried directly. Nothing to build now — just don't be surprised when this is the table that needs it first.

```text
The capstone project — a music streaming metadata and recommendation API — is the centerpiece of this plan, designed to signal research capability to AI/ML-focused professors rather than to employers.
```