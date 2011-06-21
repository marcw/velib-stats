CREATE TABLE velib_station (
  id integer NOT NULL,
  bonus boolean NOT NULL,
  fullAddress text NOT NULL,
  address varchar(255) NOT NULL,
  coord point NOT NULL,
  name varchar(255) NOT NULL,
  open boolean NOT NULL,
  created_at timestamp NOT NULL DEFAULT now(),
  updated_at timestamp NOT NULL DEFAUlT now(),
  PRIMARY KEY (id)
);

CREATE TABLE velib_station_data (
  station_id integer NOT NULL REFERENCES velib_station(id),
  created_at timestamp NOT NULL DEFAULT now(),
  available integer NOT NULL CHECK (available >= 0),
  free integer NOT NULL CHECK (free >= 0),
  total integer NOT NULL CHECK (total > 0),
  ticket integer NOT NULL,
  PRIMARY KEY (station_id, created_at)
);

CREATE OR REPLACE FUNCTION update_updated_at() RETURNS TRIGGER AS $$
  BEGIN
    NEW.updated_at := now();

    RETURN NEW;
  END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER updated_at_velib_station BEFORE UPDATE ON velib_station FOR EACH ROW EXECUTE PROCEDURE update_updated_at();

