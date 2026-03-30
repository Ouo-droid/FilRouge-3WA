-- Migration : création de la table absence
-- Date : 2026-03-28

CREATE TABLE IF NOT EXISTS absence (
    id         UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id    UUID         NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    reason     VARCHAR(255),
    startdate  DATE         NOT NULL,
    enddate    DATE         NOT NULL,
    createdat  TIMESTAMP    NOT NULL DEFAULT NOW(),
    CONSTRAINT absence_dates_check CHECK (enddate >= startdate)
);

CREATE INDEX IF NOT EXISTS idx_absence_user_id   ON absence (user_id);
CREATE INDEX IF NOT EXISTS idx_absence_startdate ON absence (startdate);
CREATE INDEX IF NOT EXISTS idx_absence_enddate   ON absence (enddate);
