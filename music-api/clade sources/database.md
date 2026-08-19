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