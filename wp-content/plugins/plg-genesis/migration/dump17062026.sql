--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: estudiantes; Type: TABLE; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE TABLE estudiantes (
    id integer NOT NULL,
    id_contacto integer,
    doc_identidad character varying(15),
    id_estudiante character varying,
    nombre1 character varying(50),
    nombre2 character varying(50),
    apellido1 character varying(50),
    apellido2 character varying(50),
    celular character varying(20),
    email character varying(100),
    ciudad character varying(50),
    iglesia character varying(100),
    fecha_registro timestamp without time zone DEFAULT now(),
    estado_civil character varying(20),
    escolaridad character varying(50),
    ocupacion character varying(100)
);


ALTER TABLE public.estudiantes OWNER TO emmaus;

--
-- Name: estudiantes_id_seq; Type: SEQUENCE; Schema: public; Owner: emmaus
--

CREATE SEQUENCE estudiantes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.estudiantes_id_seq OWNER TO emmaus;

--
-- Name: estudiantes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: emmaus
--

ALTER SEQUENCE estudiantes_id_seq OWNED BY estudiantes.id;


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY estudiantes ALTER COLUMN id SET DEFAULT nextval('estudiantes_id_seq'::regclass);


--
-- Name: estudiantes_pkey; Type: CONSTRAINT; Schema: public; Owner: emmaus; Tablespace: 
--

ALTER TABLE ONLY estudiantes
    ADD CONSTRAINT estudiantes_pkey PRIMARY KEY (id);


--
-- Name: estudiantes_id_contacto_fkey; Type: FK CONSTRAINT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY estudiantes
    ADD CONSTRAINT estudiantes_id_contacto_fkey FOREIGN KEY (id_contacto) REFERENCES contactos(id);


--
-- Name: estudiantes; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE estudiantes FROM PUBLIC;
REVOKE ALL ON TABLE estudiantes FROM emmaus;
GRANT ALL ON TABLE estudiantes TO emmaus;
GRANT ALL ON TABLE estudiantes TO emmaus_estudiantes;
GRANT ALL ON TABLE estudiantes TO emmaus_admin;


--
-- Name: estudiantes_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE estudiantes_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE estudiantes_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE estudiantes_id_seq TO emmaus;
GRANT ALL ON SEQUENCE estudiantes_id_seq TO emmaus_admin;


--
-- PostgreSQL database dump complete
--

