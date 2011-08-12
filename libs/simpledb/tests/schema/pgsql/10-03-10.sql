--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: select; Type: TABLE; Schema: public; Owner: crystal; Tablespace: 
--

CREATE TABLE "select" (
    delete character varying(2)
);


ALTER TABLE public."select" OWNER TO crystal;

--
-- Name: users_all; Type: TABLE; Schema: public; Owner: crystal; Tablespace: 
--

CREATE TABLE users_all (
    name character varying(32),
    deleted smallint DEFAULT 0,
    owner smallint
);


ALTER TABLE public.users_all OWNER TO crystal;

--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

