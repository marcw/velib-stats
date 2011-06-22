-- Dumped with the following format options:
-- pg_dump -n vlib -s -c -O
--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = vlib, pg_catalog;

ALTER TABLE ONLY vlib.velib_station_data DROP CONSTRAINT velib_station_data_station_id_fkey;
DROP TRIGGER updated_at_velib_station ON vlib.velib_station;
ALTER TABLE ONLY vlib.velib_station DROP CONSTRAINT velib_station_pkey;
ALTER TABLE ONLY vlib.velib_station_data DROP CONSTRAINT velib_station_data_pkey;
DROP TABLE vlib.velib_station_data;
DROP TABLE vlib.velib_station;
DROP FUNCTION vlib.update_updated_at();
DROP SCHEMA vlib;
--
-- Name: vlib; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA vlib;


SET search_path = vlib, pg_catalog;

--
-- Name: update_updated_at(); Type: FUNCTION; Schema: vlib; Owner: -
--

CREATE FUNCTION update_updated_at() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
  BEGIN
    NEW.updated_at := now();

    RETURN NEW;
  END;
$$;


SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: velib_station; Type: TABLE; Schema: vlib; Owner: -; Tablespace: 
--

CREATE TABLE velib_station (
    id integer NOT NULL,
    bonus boolean NOT NULL,
    fulladdress text NOT NULL,
    address character varying(255) NOT NULL,
    coord point NOT NULL,
    name character varying(255) NOT NULL,
    open boolean NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL
);


--
-- Name: velib_station_data; Type: TABLE; Schema: vlib; Owner: -; Tablespace: 
--

CREATE TABLE velib_station_data (
    station_id integer NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    available integer NOT NULL,
    free integer NOT NULL,
    total integer NOT NULL,
    ticket integer NOT NULL,
    CONSTRAINT velib_station_data_available_check CHECK ((available >= 0)),
    CONSTRAINT velib_station_data_free_check CHECK ((free >= 0)),
    CONSTRAINT velib_station_data_total_check CHECK ((total > 0))
);


--
-- Name: velib_station_data_pkey; Type: CONSTRAINT; Schema: vlib; Owner: -; Tablespace: 
--

ALTER TABLE ONLY velib_station_data
    ADD CONSTRAINT velib_station_data_pkey PRIMARY KEY (station_id, created_at);


--
-- Name: velib_station_pkey; Type: CONSTRAINT; Schema: vlib; Owner: -; Tablespace: 
--

ALTER TABLE ONLY velib_station
    ADD CONSTRAINT velib_station_pkey PRIMARY KEY (id);


--
-- Name: updated_at_velib_station; Type: TRIGGER; Schema: vlib; Owner: -
--

CREATE TRIGGER updated_at_velib_station
    BEFORE UPDATE ON velib_station
    FOR EACH ROW
    EXECUTE PROCEDURE update_updated_at();


--
-- Name: velib_station_data_station_id_fkey; Type: FK CONSTRAINT; Schema: vlib; Owner: -
--

ALTER TABLE ONLY velib_station_data
    ADD CONSTRAINT velib_station_data_station_id_fkey FOREIGN KEY (station_id) REFERENCES velib_station(id);


--
-- PostgreSQL database dump complete
--

