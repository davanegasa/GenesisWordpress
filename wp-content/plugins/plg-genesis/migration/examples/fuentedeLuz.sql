--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

--
-- Name: generar_numero_acta(); Type: FUNCTION; Schema: public; Owner: emmaus
--

CREATE FUNCTION generar_numero_acta() RETURNS text
    LANGUAGE plpgsql
    AS $_$
DECLARE
    anio TEXT;
    consecutivo INTEGER;
    nuevo_numero TEXT;
BEGIN
    anio := EXTRACT(YEAR FROM CURRENT_DATE)::TEXT;
    
    -- Obtener el último consecutivo del año actual
    SELECT COALESCE(MAX(
        CAST(SUBSTRING(numero_acta FROM '\d+$') AS INTEGER)
    ), 0) INTO consecutivo
    FROM actas_diplomas
    WHERE numero_acta LIKE anio || '-%';
    
    -- Incrementar
    consecutivo := consecutivo + 1;
    
    -- Formato: YYYY-NNN (ej: 2025-001)
    nuevo_numero := anio || '-' || LPAD(consecutivo::TEXT, 3, '0');
    
    RETURN nuevo_numero;
END;
$_$;


ALTER FUNCTION public.generar_numero_acta() OWNER TO emmaus;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: actas_diplomas; Type: TABLE; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE TABLE actas_diplomas (
    id integer NOT NULL,
    numero_acta character varying(50) NOT NULL,
    fecha_acta date DEFAULT ('now'::text)::date NOT NULL,
    contacto_id integer,
    tipo_acta character varying(50) DEFAULT 'cierre'::character varying NOT NULL,
    total_diplomas integer DEFAULT 0 NOT NULL,
    observaciones text,
    estado character varying(20) DEFAULT 'activa'::character varying NOT NULL,
    created_by integer,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    CONSTRAINT chk_estado_acta CHECK (((estado)::text = ANY ((ARRAY['activa'::character varying, 'anulada'::character varying])::text[]))),
    CONSTRAINT chk_tipo_acta CHECK (((tipo_acta)::text = ANY ((ARRAY['cierre'::character varying, 'graduacion'::character varying, 'regular'::character varying])::text[])))
);


ALTER TABLE public.actas_diplomas OWNER TO emmaus;

--
-- Name: TABLE actas_diplomas; Type: COMMENT; Schema: public; Owner: emmaus
--

COMMENT ON TABLE actas_diplomas IS 'Registro formal de actas que agrupan diplomas emitidos';


--
-- Name: COLUMN actas_diplomas.numero_acta; Type: COMMENT; Schema: public; Owner: emmaus
--

COMMENT ON COLUMN actas_diplomas.numero_acta IS 'Número único de acta, formato YYYY-NNN';


--
-- Name: COLUMN actas_diplomas.tipo_acta; Type: COMMENT; Schema: public; Owner: emmaus
--

COMMENT ON COLUMN actas_diplomas.tipo_acta IS 'Tipo de acta: cierre, graduacion, regular';


--
-- Name: COLUMN actas_diplomas.estado; Type: COMMENT; Schema: public; Owner: emmaus
--

COMMENT ON COLUMN actas_diplomas.estado IS 'Estado del acta: activa o anulada';


--
-- Name: actas_diplomas_id_seq; Type: SEQUENCE; Schema: public; Owner: emmaus
--

CREATE SEQUENCE actas_diplomas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.actas_diplomas_id_seq OWNER TO emmaus;

--
-- Name: actas_diplomas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: emmaus
--

ALTER SEQUENCE actas_diplomas_id_seq OWNED BY actas_diplomas.id;


--
-- Name: contactos; Type: TABLE; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE TABLE contactos (
    id integer NOT NULL,
    nombre character varying(100),
    iglesia character varying(100),
    email character varying(100),
    celular character varying(20),
    direccion character varying(255),
    ciudad character varying(50),
    code character(10),
    fecha_registro timestamp without time zone DEFAULT now()
);


ALTER TABLE public.contactos OWNER TO emmaus;

--
-- Name: contactos_id_seq; Type: SEQUENCE; Schema: public; Owner: emmaus
--

CREATE SEQUENCE contactos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.contactos_id_seq OWNER TO emmaus;

--
-- Name: contactos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: emmaus
--

ALTER SEQUENCE contactos_id_seq OWNED BY contactos.id;


--
-- Name: cursos; Type: TABLE; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE TABLE cursos (
    id integer NOT NULL,
    nombre character varying(100),
    nivel_id integer,
    descripcion text,
    id_material character varying(20),
    id_tipo character varying(20),
    valor_costo numeric,
    valor_venta numeric,
    consecutivo integer,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone
);


ALTER TABLE public.cursos OWNER TO emmaus;

--
-- Name: cursos_id_seq; Type: SEQUENCE; Schema: public; Owner: emmaus
--

CREATE SEQUENCE cursos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.cursos_id_seq OWNER TO emmaus;

--
-- Name: cursos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: emmaus
--

ALTER SEQUENCE cursos_id_seq OWNED BY cursos.id;


--
-- Name: diplomas_entregados; Type: TABLE; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE TABLE diplomas_entregados (
    id integer NOT NULL,
    tipo character varying(50) NOT NULL,
    programa_id integer NOT NULL,
    nivel_id integer,
    version_programa integer DEFAULT 1 NOT NULL,
    estudiante_id integer,
    contacto_id integer,
    acta_id integer,
    fecha_emision date DEFAULT ('now'::text)::date NOT NULL,
    fecha_entrega date,
    entregado_por integer,
    notas text,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    CONSTRAINT chk_diploma_estudiante_required CHECK ((estudiante_id IS NOT NULL)),
    CONSTRAINT chk_nivel_requerido CHECK (((((tipo)::text = 'nivel'::text) AND (nivel_id IS NOT NULL)) OR (((tipo)::text = 'programa_completo'::text) AND (nivel_id IS NULL)))),
    CONSTRAINT chk_tipo_diploma CHECK (((tipo)::text = ANY ((ARRAY['programa_completo'::character varying, 'nivel'::character varying])::text[])))
);


ALTER TABLE public.diplomas_entregados OWNER TO emmaus;

--
-- Name: TABLE diplomas_entregados; Type: COMMENT; Schema: public; Owner: emmaus
--

COMMENT ON TABLE diplomas_entregados IS 'Registro histórico de diplomas emitidos y entregados a estudiantes';


--
-- Name: COLUMN diplomas_entregados.tipo; Type: COMMENT; Schema: public; Owner: emmaus
--

COMMENT ON COLUMN diplomas_entregados.tipo IS 'Tipo de diploma: programa_completo o nivel';


--
-- Name: COLUMN diplomas_entregados.programa_id; Type: COMMENT; Schema: public; Owner: emmaus
--

COMMENT ON COLUMN diplomas_entregados.programa_id IS 'Programa al que pertenece el diploma';


--
-- Name: COLUMN diplomas_entregados.nivel_id; Type: COMMENT; Schema: public; Owner: emmaus
--

COMMENT ON COLUMN diplomas_entregados.nivel_id IS 'Nivel específico (solo si tipo=nivel)';


--
-- Name: COLUMN diplomas_entregados.version_programa; Type: COMMENT; Schema: public; Owner: emmaus
--

COMMENT ON COLUMN diplomas_entregados.version_programa IS 'Versión del programa bajo la cual se completó';


--
-- Name: COLUMN diplomas_entregados.fecha_emision; Type: COMMENT; Schema: public; Owner: emmaus
--

COMMENT ON COLUMN diplomas_entregados.fecha_emision IS 'Fecha en que se emitió el diploma';


--
-- Name: COLUMN diplomas_entregados.fecha_entrega; Type: COMMENT; Schema: public; Owner: emmaus
--

COMMENT ON COLUMN diplomas_entregados.fecha_entrega IS 'Fecha de entrega física (NULL = pendiente)';


--
-- Name: COLUMN diplomas_entregados.entregado_por; Type: COMMENT; Schema: public; Owner: emmaus
--

COMMENT ON COLUMN diplomas_entregados.entregado_por IS 'ID del usuario WordPress que registró la entrega';


--
-- Name: diplomas_entregados_id_seq; Type: SEQUENCE; Schema: public; Owner: emmaus
--

CREATE SEQUENCE diplomas_entregados_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.diplomas_entregados_id_seq OWNER TO emmaus;

--
-- Name: diplomas_entregados_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: emmaus
--

ALTER SEQUENCE diplomas_entregados_id_seq OWNED BY diplomas_entregados.id;


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
-- Name: estudiantes_cursos; Type: TABLE; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE TABLE estudiantes_cursos (
    id integer NOT NULL,
    estudiante_id integer,
    curso_id integer,
    fecha date,
    porcentaje double precision,
    enviado boolean DEFAULT false
);


ALTER TABLE public.estudiantes_cursos OWNER TO emmaus;

--
-- Name: estudiantes_cursos_id_seq; Type: SEQUENCE; Schema: public; Owner: emmaus
--

CREATE SEQUENCE estudiantes_cursos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.estudiantes_cursos_id_seq OWNER TO emmaus;

--
-- Name: estudiantes_cursos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: emmaus
--

ALTER SEQUENCE estudiantes_cursos_id_seq OWNED BY estudiantes_cursos.id;


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
-- Name: estudiantes_programas; Type: TABLE; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE TABLE estudiantes_programas (
    id integer NOT NULL,
    estudiante_id integer NOT NULL,
    programa_id integer NOT NULL,
    fecha_inscripcion timestamp without time zone DEFAULT now()
);


ALTER TABLE public.estudiantes_programas OWNER TO emmaus;

--
-- Name: estudiantes_programas_id_seq; Type: SEQUENCE; Schema: public; Owner: emmaus
--

CREATE SEQUENCE estudiantes_programas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.estudiantes_programas_id_seq OWNER TO emmaus;

--
-- Name: estudiantes_programas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: emmaus
--

ALTER SEQUENCE estudiantes_programas_id_seq OWNED BY estudiantes_programas.id;


--
-- Name: niveles; Type: TABLE; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE TABLE niveles (
    id integer NOT NULL,
    nombre character varying(100)
);


ALTER TABLE public.niveles OWNER TO emmaus;

--
-- Name: niveles_id_seq; Type: SEQUENCE; Schema: public; Owner: emmaus
--

CREATE SEQUENCE niveles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.niveles_id_seq OWNER TO emmaus;

--
-- Name: niveles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: emmaus
--

ALTER SEQUENCE niveles_id_seq OWNED BY niveles.id;


--
-- Name: niveles_programas; Type: TABLE; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE TABLE niveles_programas (
    id integer NOT NULL,
    programa_id integer NOT NULL,
    nombre character varying(255) NOT NULL,
    version integer
);


ALTER TABLE public.niveles_programas OWNER TO emmaus;

--
-- Name: niveles_programas_id_seq; Type: SEQUENCE; Schema: public; Owner: emmaus
--

CREATE SEQUENCE niveles_programas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.niveles_programas_id_seq OWNER TO emmaus;

--
-- Name: niveles_programas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: emmaus
--

ALTER SEQUENCE niveles_programas_id_seq OWNED BY niveles_programas.id;


--
-- Name: observaciones_estudiantes; Type: TABLE; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE TABLE observaciones_estudiantes (
    id integer NOT NULL,
    estudiante_id integer NOT NULL,
    observacion text NOT NULL,
    fecha timestamp without time zone DEFAULT now(),
    usuario_id integer,
    tipo character varying(10) DEFAULT 'General'::character varying NOT NULL
);


ALTER TABLE public.observaciones_estudiantes OWNER TO emmaus;

--
-- Name: observaciones_estudiantes_id_seq; Type: SEQUENCE; Schema: public; Owner: emmaus
--

CREATE SEQUENCE observaciones_estudiantes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.observaciones_estudiantes_id_seq OWNER TO emmaus;

--
-- Name: observaciones_estudiantes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: emmaus
--

ALTER SEQUENCE observaciones_estudiantes_id_seq OWNED BY observaciones_estudiantes.id;


--
-- Name: programas; Type: TABLE; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE TABLE programas (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion text,
    current_version integer,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.programas OWNER TO emmaus;

--
-- Name: programas_asignaciones; Type: TABLE; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE TABLE programas_asignaciones (
    id integer NOT NULL,
    programa_id integer NOT NULL,
    estudiante_id integer,
    contacto_id integer,
    fecha_asignacion timestamp without time zone DEFAULT now(),
    version integer,
    activo boolean DEFAULT true NOT NULL,
    CONSTRAINT chk_uno_o_otro CHECK ((((estudiante_id IS NOT NULL) AND (contacto_id IS NULL)) OR ((contacto_id IS NOT NULL) AND (estudiante_id IS NULL))))
);


ALTER TABLE public.programas_asignaciones OWNER TO emmaus;

--
-- Name: COLUMN programas_asignaciones.programa_id; Type: COMMENT; Schema: public; Owner: emmaus
--

COMMENT ON COLUMN programas_asignaciones.programa_id IS 'ID del programa asignado';


--
-- Name: COLUMN programas_asignaciones.estudiante_id; Type: COMMENT; Schema: public; Owner: emmaus
--

COMMENT ON COLUMN programas_asignaciones.estudiante_id IS 'ID del estudiante al que se asigna el programa, si corresponde';


--
-- Name: COLUMN programas_asignaciones.contacto_id; Type: COMMENT; Schema: public; Owner: emmaus
--

COMMENT ON COLUMN programas_asignaciones.contacto_id IS 'ID del contacto al que se asigna el programa, si corresponde';


--
-- Name: COLUMN programas_asignaciones.fecha_asignacion; Type: COMMENT; Schema: public; Owner: emmaus
--

COMMENT ON COLUMN programas_asignaciones.fecha_asignacion IS 'Fecha en la que se asigna el programa';


--
-- Name: COLUMN programas_asignaciones.activo; Type: COMMENT; Schema: public; Owner: emmaus
--

COMMENT ON COLUMN programas_asignaciones.activo IS 'Indica si la asignación está activa. Los programas inactivos se ocultan pero mantienen el historial de progreso.';


--
-- Name: programas_asignaciones_id_seq; Type: SEQUENCE; Schema: public; Owner: emmaus
--

CREATE SEQUENCE programas_asignaciones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.programas_asignaciones_id_seq OWNER TO emmaus;

--
-- Name: programas_asignaciones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: emmaus
--

ALTER SEQUENCE programas_asignaciones_id_seq OWNED BY programas_asignaciones.id;


--
-- Name: programas_cursos; Type: TABLE; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE TABLE programas_cursos (
    id integer NOT NULL,
    programa_id integer NOT NULL,
    curso_id integer NOT NULL,
    nivel_id integer,
    consecutivo integer NOT NULL,
    version integer
);


ALTER TABLE public.programas_cursos OWNER TO emmaus;

--
-- Name: programas_cursos_id_seq; Type: SEQUENCE; Schema: public; Owner: emmaus
--

CREATE SEQUENCE programas_cursos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.programas_cursos_id_seq OWNER TO emmaus;

--
-- Name: programas_cursos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: emmaus
--

ALTER SEQUENCE programas_cursos_id_seq OWNED BY programas_cursos.id;


--
-- Name: programas_id_seq; Type: SEQUENCE; Schema: public; Owner: emmaus
--

CREATE SEQUENCE programas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.programas_id_seq OWNER TO emmaus;

--
-- Name: programas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: emmaus
--

ALTER SEQUENCE programas_id_seq OWNED BY programas.id;


--
-- Name: programas_prerequisitos; Type: TABLE; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE TABLE programas_prerequisitos (
    id integer NOT NULL,
    programa_id integer NOT NULL,
    prerequisito_id integer NOT NULL
);


ALTER TABLE public.programas_prerequisitos OWNER TO emmaus;

--
-- Name: programas_prerequisitos_id_seq; Type: SEQUENCE; Schema: public; Owner: emmaus
--

CREATE SEQUENCE programas_prerequisitos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.programas_prerequisitos_id_seq OWNER TO emmaus;

--
-- Name: programas_prerequisitos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: emmaus
--

ALTER SEQUENCE programas_prerequisitos_id_seq OWNED BY programas_prerequisitos.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE TABLE users (
    id integer NOT NULL,
    username character varying(255) NOT NULL,
    password character varying(255) NOT NULL
);


ALTER TABLE public.users OWNER TO emmaus;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: emmaus
--

CREATE SEQUENCE users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.users_id_seq OWNER TO emmaus;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: emmaus
--

ALTER SEQUENCE users_id_seq OWNED BY users.id;


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY actas_diplomas ALTER COLUMN id SET DEFAULT nextval('actas_diplomas_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY contactos ALTER COLUMN id SET DEFAULT nextval('contactos_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY cursos ALTER COLUMN id SET DEFAULT nextval('cursos_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY diplomas_entregados ALTER COLUMN id SET DEFAULT nextval('diplomas_entregados_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY estudiantes ALTER COLUMN id SET DEFAULT nextval('estudiantes_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY estudiantes_cursos ALTER COLUMN id SET DEFAULT nextval('estudiantes_cursos_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY estudiantes_programas ALTER COLUMN id SET DEFAULT nextval('estudiantes_programas_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY niveles ALTER COLUMN id SET DEFAULT nextval('niveles_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY niveles_programas ALTER COLUMN id SET DEFAULT nextval('niveles_programas_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY observaciones_estudiantes ALTER COLUMN id SET DEFAULT nextval('observaciones_estudiantes_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY programas ALTER COLUMN id SET DEFAULT nextval('programas_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY programas_asignaciones ALTER COLUMN id SET DEFAULT nextval('programas_asignaciones_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY programas_cursos ALTER COLUMN id SET DEFAULT nextval('programas_cursos_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY programas_prerequisitos ALTER COLUMN id SET DEFAULT nextval('programas_prerequisitos_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY users ALTER COLUMN id SET DEFAULT nextval('users_id_seq'::regclass);


--
-- Data for Name: actas_diplomas; Type: TABLE DATA; Schema: public; Owner: emmaus
--

COPY actas_diplomas (id, numero_acta, fecha_acta, contacto_id, tipo_acta, total_diplomas, observaciones, estado, created_by, created_at, updated_at) FROM stdin;
\.


--
-- Name: actas_diplomas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('actas_diplomas_id_seq', 1, false);


--
-- Data for Name: contactos; Type: TABLE DATA; Schema: public; Owner: emmaus
--

COPY contactos (id, nombre, iglesia, email, celular, direccion, ciudad, code, fecha_registro) FROM stdin;
1	Luis David Paez	BALCANES	servimascotaspc@gmail.com	300 2889219	Calle 72 a 66 -25 apto 501	Bogotá	07        	2025-01-22 21:37:59.801577
2	Galaxius Castañeda 	Bautista La Gracia Chia 	iglesialagraciachia@gmail.com	3003966776	Cra 4# 10-38	Bogotá-Chia	58        	2025-01-22 21:37:59.801577
3	Luis David Páez 	SEDID	servimascotaspc@gmail.com	300 2889219		Bogotá	08        	2025-02-11 12:11:49.32778
4	Luis David Páez 	EL CAMINO 	servimascotaspc@gmail.com	300 2889219		Bogotá	09        	2025-02-11 12:13:24.776086
5	Carlos Alberto Acevedo Alvarez	APOSENTO ALTO LA VID 		3204265211	carrera 13#164b-21	Bogotá	11        	2025-02-11 12:23:28.273204
6	Jeisson Mendoza	APOSENTO ALTO JJ VARGAS 		350 4403568	Av Calle 68 #65-10	Bogotá	18        	2025-02-12 10:12:15.849876
7	Yolanda Bejarano Salamanca 	APOSENTO ALTO VENECIA 	jefada3@hotmail.com	314 4335725	cra 2#1-04 int 4 apto 404 	Madrid/ Cundinamarca	22        	2025-02-12 10:16:30.903591
8	Guillermo Barbosa 	APOSENTO ALTO SUBA 	guillermoelpotebarbosa@hotmail.com	310 8842329	calle 151 b #117-62 apto 302	Bogotá	23        	2025-02-12 10:21:38.941667
9	Matha Bibiana Barriga	GIMNASIO EL SHADDAI	coord.academica@gimnasioshaddai.edu.co	3142594589		Bogotá	105       	2025-02-15 13:59:19.380133
10	Magnolia López	APOSENTO ALTO BERLIN	paulaycarito@hotmail.com	3177270871		Bogotá	28        	2025-02-15 14:01:58.848556
11	Tatiana Rodriguez 	LOCAL BIBLICO ALQUERIA	tatys_rodriguez@yahoo.com	3013710631		Bogotá	30        	2025-02-15 14:03:02.463053
12	Stella Rodriguez 	APOSENTO ALTO ORQUIDEAS	stelladiazrodriguez@hotmail.com	3108597543		Bogotá	36        	2025-02-15 14:04:20.203
13	Mónica Wilches 	APOSENTO ALTO MUZU		321 4495138	Calle 77 b sur #14A25 piso 2 barrio la marichuela	Bogotá	37        	2025-02-15 14:07:04.134906
14	Carolina Dimate Murcia 	VIDA CHURCH	carodimur@gmail.com	321 9428414		Soacha	41        	2025-02-15 14:12:05.513726
15	Fabian Alberto González 	APOSENTO ALTO 	fagoat7@gmail.com	-	-	Riohacha 	42        	2025-02-15 14:15:37.765994
16	Mabel Bandera	COMUNIDAD DE CRECIMIENTO CRISTIANO		318 6020884		Soacha 	46        	2025-02-15 14:17:53.982435
17	Fredy Rojas 	APOSENTO ALTO FUSAGASUGA	juda691@hotmail.com	310 3496757	-	Fusagasugá	60        	2025-02-15 14:22:22.247909
18	Pedro Daniel Chávez Silva	APOSENTO ALTO CENTRO	pedrokal@hotmail.com	3138774482	-	Bogotá 	61        	2025-02-15 14:24:23.05016
19	Sandra Patricia Alfaro	OFICINA BOGOTÁ	support@escuelaemmaus.com	311 4529067	Av Calle 68 #65-10	Bogotá	99        	2025-02-15 14:33:18.59753
20	Sandra Milena Vanegas	HOMESCHOOL CAJICÁ		304 5308163		Cajicá	40        	2025-02-15 14:37:36.781708
21	Saúl Amaya	BUENA SEMILLA DE COCOTOBA		-	-	Vichada-Cocotobá	44        	2025-02-15 14:39:09.991133
22	Jesús Roa	CASA DE ORACIÓN 		+34 642 75 81 41		Bogotá	74        	2025-02-15 16:04:59.602772
23	Juan David Sánchez Osorio	Iglesia Bautista el Camino	juandasanchez360@gmail.com	3158919435	calle 64c# 105d 75	Bogotá	68        	2025-03-19 16:26:24.173258
24	Luis David Paez	Centro Gaviotas	servimascotaspc@gmail.com	300 2889219		Bogotá	06        	2025-06-12 15:56:52.792792
\.


--
-- Name: contactos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('contactos_id_seq', 24, true);


--
-- Data for Name: cursos; Type: TABLE DATA; Schema: public; Owner: emmaus
--

COPY cursos (id, nombre, nivel_id, descripcion, id_material, id_tipo, valor_costo, valor_venta, consecutivo, created_at, updated_at, deleted_at) FROM stdin;
1	VH1L1y2	\N	Ver y Hacer 1 L 1 y 2	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
2	VH1L3y4	\N	Ver y Hacer 1 L 3 y 4	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
3	VH1L5,6,7	\N	Ver y Hacer 1 L 5,6,7	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
4	VH2L1y2	\N	Ver y Hacer 2 L 1 y 2	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
5	VH2L3y4	\N	Ver y Hacer 2 L 3 y 4	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
6	VH2L5,6,7	\N	Ver y Hacer 2 L 5,6,7	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
7	HC1L1y2	\N	Hora del Cuento 1 L 1 y 2 	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
8	HC1L3y4	\N	Hora del cuento 1 L 3 y 4	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
9	HC1L5,6,7	\N	Hora del Cuento 1 L 5,6,7	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
10	HC2L1y2	\N	Hora del Cuento 2 L 1 y 2	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
11	HC2L3y4	\N	Hora del Cuento 2 L 3 y 4	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
12	HC2L5,6,7	\N	Hora del Cuento 2 L 5,6,7	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
13	EXP1L1y2	\N	Exploradores de la Biblia 1 L 1 y 2	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
14	EXP1L3y4	\N	Exploradores de la Biblia 1 L 3 y 4	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
15	EXP1L5y6	\N	Exploradores de la Biblia 1 L 5 y 6 	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
16	EXP1L7y8	\N	Exploradores de la Biblia 1 L 7 y 8	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
17	EXP1L9y10	\N	Exploradores de la Biblia 1 L 9 y 10	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
18	EXP2L1y2	\N	Exploradores de la Biblia 2 L 1 y 2	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
19	EXP2L3y4	\N	Exploradores de la Biblia 2 L 3 y 4	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
20	EXP2L5y6	\N	Exploradores de la Biblia 2 L 5 y 6	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
21	EXP2L7y8	\N	Exploradores de la Biblia 2 L 7 y 8	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
22	EXP2L9y10	\N	Exploradores de la Biblia 2 L 9 y 10	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
23	EXP2L11y12	\N	Exploradores de la Biblia 2 L 11 y 12	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
24	VENL1y2	\N	Vencedores L 1 y 2	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
25	VENL3y4	\N	Vencedores L 3 y 4	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
26	VH1L1y2	1	Ver y Hacer 1 Lc 1 y 2	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
27	VH1L3y4	1	Ver y Hacer 1 Lc 3 y 4	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
28	VH1L5,6,7	1	Ver y Hacer 1 Lc 5 y 6	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
29	VH2L1y2	2	Ver y Hacer 2 Lc 1 y 2	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
30	VH2L3y4	2	Ver y Hacer 2 Lc 3 y 4	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
31	VH2L5,6,7	2	Ver y Hacer 2 Lc 5,6,7	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
32	HC1L1y2	1	Hora del Cuento 1 Lc 1 y 2	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
33	HC1L3y4	1	Hora del Cuento 1 Lc 3 y 4	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
34	HC1L5,6,7	1	Hora del Cuento 1 Lc 5,6,7	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
35	HC2L1y2	2	Hora del Cuento 2 Lc 1 y 2	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
36	HC2L3y4	2	Hora del Cuento 2 Lc 3 y 4	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
37	HC2L5,6,7	2	Hora del Cuento 2 Lc 5,6,7	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
38	EXP1L1y2	1	Exploradores de la Biblia 1 Lc 1 y 2	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
39	EXP1L3y4	1	Exploradores de la Biblia 1 Lc 3 y 4	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
40	EXP1L5y6	1	Exploradores de la Biblia 1 Lc 5 y 6	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
41	EXP1L7y8	1	Exploradores de la Biblia 1 Lc 7 y 8	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
42	EXP1L9y10	1	Exploradores de la Biblia 1 Lc  9 y 10	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
43	EXP2L1y2	2	Exploradores de la Biblia 2 Lc 1 y 2	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
44	EXP2L3y4	2	Exploradores de la Biblia 2 Lc 3 y 4	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
45	EXP2L5y6	2	Exploradores de la Biblia 2 Lc 5 y 6	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
46	EXP2L7y8	2	Exploradores de la Biblia 2 Lc 7 y 8	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
47	EXP2L9y10	2	Exploradores de la Biblia 2 Lc  9 y 10	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
48	EXP2L11y12	2	Exploradores de la Biblia 2 Lc 11 y 12	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
49	VENL1y2	3	Vencedores Lc 1 y 2	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
50	VENL3y4	3	Vencedores Lc 3 y 4	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
51	VENL5y6	3	Vencedores Lc 5 y 6	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
52	VENL7y8	3	Vencedores Lc 7 y 8	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
53	VENL9y10	3	Vencedores Lc 9 y 10	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
54	VENL11y12	3	Vencedores Lc 11 y 12	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
55	CVEL1	3	Camino a la Vida Eterna Lc 1	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
56	CVEL2,3,4	3	Camino a la Vida Eterna Lc 2,3,4	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
57	CVEL5,6,7	3	Camino a la Vida Eterna Lc 5,6,7	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
58	PLCL1V	3	Un País Llamado el Cielo Lc 1(viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
59	PLCL2y3V	3	Un País Llamado el Cielo Lc 2 y 3(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
60	PLCL4y5V	3	Un País Llamado el Cielo Lc 4 y 5(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
61	PLCL6y7V	3	Un País Llamado el Cielo Lc 6 y 7(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
62	PLCL8y9V	3	Un País Llamado el Cielo Lc 8 y 9(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
63	PLCL10y11V	3	Un País Llamado el Cielo Lc 10y 11(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
64	PLCL12y13V	3	Un País Llamado el Cielo Lc 12y13(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
65	PLCL14y15V	3	Un País Llamado el Cielo Lc 14y15(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
66	PLCL1y2	3	Un País Llamado el Cielo Lc 1 y 2	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
67	PLCL3y4	3	Un País Llamado el Cielo Lc 3 y 4	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
68	PLCL5y6	3	Un País Llamado el Cielo Lc 5 y 6	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
69	PLCL7y8	3	Un País Llamado el Cielo Lc 7 y 8	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
70	PLCL9y10	3	Un País Llamado el Cielo Lc 9 y 10	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
71	PLCL11y12	3	Un País Llamado el Cielo Lc 11 y 12	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
72	PLCL13,14,15	3	Un País Llamado el Cielo Lc 13,14,15	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
73	RDL	3	Rayos de Luz Lc 1	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
74	VDCL1V	3	Vida de Cristo Lc 1(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
75	VDCL2y3V	3	Vida de Cristo Lc 2 y 3(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
76	VDCL4y5V	3	Vida de Cristo Lc 4 y 5(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
77	VDCL6y7V	3	Vida de Cristo Lc 6 y 7(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
78	VDCL8y9V	3	Vida de Cristo Lc 8 y 9(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
79	VDCL1y2	3	Vida de Cristo Lc 1 y 2	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
80	VDCL3y4	3	Vida de Cristo Lc 3 y 4	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
81	VDCL5y6	3	Vida de Cristo Lc 5 y 6	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
82	VDCL7,8,9	3	Vida de Cristo Lc 7,8,9	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
83	NVCL1y2	3	Nueva Vida en Cristo Lc 1 y 2	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
84	NVCL3y4	3	Nueva Vida en Cristo Lc 3 y 4	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
85	NVCL5y6	3	Nueva Vida en Cristo Lc 5 y 6	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
86	NVCL7y8	3	Nueva Vida en Cristo Lc 7 y 8	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
87	NVCL9y10	3	Nueva Vida en Cristo Lc 9 y 10	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
88	NVCL11y12	3	Nueva Vida en Cristo Lc 11 y 12	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
89	NVCL13y14	3	Nueva Vida en Cristo Lc 13 y 14	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
90	NVCL15y16	3	Nueva Vida en Cristo Lc 15 y 16	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
91	NVCL17y18	3	Nueva Vida en Cristo Lc 17 y 18	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
92	NVCL19y20	3	Nueva Vida en Cristo Lc 19 y 20	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
93	NVCL21y22	3	Nueva Vida en Cristo Lc 21 y 22	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
94	NVCL23,24,25	3	Nueva Vida en Cristo Lc 23 y 24	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
95	NVCL1V	3	Nueva Vida en Cristo Lc 1(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
96	NVCL2y3V	3	Nueva Vida en Cristo Lc 2 y 3(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
97	NVCL4y5V	3	Nueva Vida en Cristo Lc 4 y 5(Viejo)\n	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
98	NVCL6y7V	3	Nueva Vida en Cristo Lc 6 y 7(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
99	NVCL8y9V	3	Nueva Vida en Cristo Lc 8 y 9(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
100	NVCL10y11V	3	Nueva Vida en Cristo Lc 10 y 11(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
101	NVCL12y13V	3	Nueva Vida en Cristo Lc 12 y 13(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
102	NVCL14y15V	3	Nueva Vida en Cristo Lc 14 y 15(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
103	NVCL16y17V	3	Nueva Vida en Cristo Lc 16 y 17(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
104	NVCL18y19V	3	Nueva Vida en Cristo Lc 18 y 19(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
105	NVCL20y21V	3	Nueva Vida en Cristo Lc 20 y 21(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
106	NVCL22y23V	3	Nueva Vida en Cristo Lc 22 y 23(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
107	NVCL24y25V	3	Nueva Vida en Cristo Lc 24 y 25(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
108	VCPL1,2,3	3	Vida Cristiana Práctica Lc 1,2,3	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
109	VCPL4,5,6	3	Vida Cristiana Práctica Lc 4,5,6	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
110	VCPL7,8,9	3	Vida Cristiana Práctica Lc 7,8,9	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
111	VCPL10,11,12	3	Vida Cristiana Práctica Lc 10,11,12	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
112	VCPL13,14,15	3	Vida Cristiana Práctica Lc 13,14,15	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
113	VCPL16,17,18	3	Vida Cristiana Práctica Lc 16,17,18	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
114	VCPL19,20,21	3	Vida Cristiana Práctica Lc 19,20,21	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
115	VCPL22,23,24	3	Vida Cristiana Práctica Lc 22,23,24	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
116	VCPL1y2V	3	Vida Cristiana Práctica Lc 1 y 2(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
117	VCPL3y4V	3	Vida Cristiana Práctica Lc 3 y 4(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
118	VCPL5y6V	3	Vida Cristiana Práctica Lc 5 y 6(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
119	VCPL7y8V	3	Vida Cristiana Práctica Lc 7 y 8(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
120	VCPL9y10V	3	Vida Cristiana Práctica Lc 9 y 10(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
121	VCPL11y12V	3	Vida Cristiana Práctica Lc 11 y 12(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
122	VCPL13y14V	3	Vida Cristiana Práctica Lc 13 y 14(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
123	VCPL15y16V	3	Vida Cristiana Práctica Lc 15 y 16(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
125	VCPL19y20V	3	Vida Cristiana Práctica Lc 19 y 20(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
126	VCPL21y22V	3	Vida Cristiana Práctica Lc 21 y 22(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
127	VCPL23y24V	3	Vida Cristiana Práctica Lc 23 y 24(Viejo)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
128	GCVL1y2	3	Ganando la Carrera de la Vida Lc 1 y 2	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
129	GCVL3y4	3	Ganando la Carrera de la Vida Lc 3 y 4	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
130	GCVL5y6	3	Ganando la Carrera de la Vida Lc 5 y 6	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
131	PDHL1	3	Pescadores de Hombres Lc 1	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
132	PDHL2	3	Pescadores de Hombres Lc 2	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
133	PDHL3	3	Pescadores de Hombres Lc 3	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
134	DDSL1	3	Doctrinas de la Salvación Lc 1 Arrepentimiento	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
135	DDSL2	3	Doctrinas de la Salvación Lc 2 Fe	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
136	DDSL3	3	Doctrinas de la Salvación Lc 3 Regeneración 	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
137	DDSL4	3	Doctrinas de la Salvación Lc 4 Justificación	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
138	DDSL5	3	Doctrinas de la Salvación Lc 5 Adopción 	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
139	DDSL6	3	Doctrinas de la Salvación Lc 6 Oración	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
140	DDSL7	3	Doctrinas de la Salvación Lc 7 Santificación	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
141	ANML1y2	3	Amor Noviazgo y Matrimonio Lc 1 y 2	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
142	ANML3y4	3	Amor Noviazgo y Matrimonio Lc 3 y 4	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
143	ANML5y6	3	Amor Noviazgo y Matrimonio Lc 5 y 6	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
144	ANML7y8	3	Amor Noviazgo y Matrimonio Lc 7 y 8	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
145	ANML9y10	3	Amor Noviazgo y Matrimonio Lc 9 y 10	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
146	ANML11y12	3	Amor Noviazgo y Matrimonio Lc 11 y 12	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
147	MDEL1y2	3	Mundo de los Espíritus Lc 1 y 2	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
148	MDEL3y4	3	Mundo de los Espíritus Lc 3 y 4	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
149	MDEL5y6	3	Mundo de los Espíritus Lc 5 y 6	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
124	VCPL17y18V	3	Vida Cristiana Práctica Lc 17 y 18(A)	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
150	Diploma Ver y Hacer 1	1	Diploma Ver y Hacer 1	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
151	Diploma Ver y Hacer 2	2	Diploma Ver y Hacer 2	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
152	Diploma Hora del Cuento 1	1	Diploma Hora del Cuento 1	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
153	Diploma Hora del Cuento 2	2	Diploma Hora del Cuento 2	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
154	Diploma Exploradores 1	1	Diploma Exploradores 1	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
155	Diploma Exploradores 2	2	Diploma Exploradores 2	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
156	Diploma Camino Vida Eterna	3	Diploma Camino Vida Eterna	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
157	Diploma Vencedores 	3	Diploma Vencedores 	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
158	Diploma Pais llamado Cielo	3	Diploma Pais llamado Cielo	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
159	Diploma Rayo de Luz	3	Diploma Rayo de Luz	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
160	Diploma Vida de Cristo	3	Diploma Vida de Cristo	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
161	Diploma Nueva Vida en Cristo 	3	Diploma Nueva Vida en Cristo 	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
162	Diploma Vida Cristiana Práctica	3	Diploma Vida Cristiana Práctica	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
163	Diploma Amor, Noviazgo y Matrimonio	3	Diploma Amor, Noviazgo y Matrimonio	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
164	Diploma Ganando la Carrera de la Vida	3	Diploma Ganando la Carrera de la Vida	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
165	Diploma Doctrinas de la Salvación	3	Diploma Doctrinas de la Salvación	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
166	Diploma Mundo de los Espíritus	3	Diploma Mundo de los Espíritus	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
167	Diploma Pescadores de Hombres 	3	Diploma Pescadores de Hombres 	\N	\N	\N	\N	\N	2025-10-08 14:36:21.279099	2025-10-08 14:36:21.279099	\N
\.


--
-- Name: cursos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('cursos_id_seq', 167, true);


--
-- Data for Name: diplomas_entregados; Type: TABLE DATA; Schema: public; Owner: emmaus
--

COPY diplomas_entregados (id, tipo, programa_id, nivel_id, version_programa, estudiante_id, contacto_id, acta_id, fecha_emision, fecha_entrega, entregado_por, notas, created_at, updated_at) FROM stdin;
\.


--
-- Name: diplomas_entregados_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('diplomas_entregados_id_seq', 1, false);


--
-- Data for Name: estudiantes; Type: TABLE DATA; Schema: public; Owner: emmaus
--

COPY estudiantes (id, id_contacto, doc_identidad, id_estudiante, nombre1, nombre2, apellido1, apellido2, celular, email, ciudad, iglesia, fecha_registro, estado_civil, escolaridad, ocupacion) FROM stdin;
1	2	1000850610	000001	Maria 	Paula	Castañeda	Caballero	3005740287	lianaccv@gmail.com	Bogotá-Chia 	Bautista la Gracia Chia 	2024-12-03 10:19:48.355971	\N	\N	\N
2	1	0	000002	Jorge 	Ivan	Acevedo	Hernández 	0	.@gmail.com	Bogotá 	No aplica 	2024-12-10 15:07:11.34292	\N	\N	\N
4	20		000004	TATIANA		CLAVIJO	CAMERO			CAJICA	APOSENTO ALTO CAJICA	2025-03-13 12:15:46.401299	\N	\N	\N
5	20		000005	JUAN	ALEJANDRO	MENDEZ	CUBIDES			CAJICA	APOSENTO ALTO CAJICA	2025-03-13 12:35:09.401375	\N	\N	\N
6	20		000006	PABLO 	ANDRES	MENDEZ	CUBIDES			CAJICÁ	APOSENTO ALTO CAJICA	2025-03-13 12:38:03.920817	\N	\N	\N
7	20		000007	ALEJANDRA		CAICEDO				CAJICÁ	APOSENTO ALTO CAJICA	2025-03-13 12:42:15.502173	\N	\N	\N
8	20		000008	MARTÍN 	FELIPE	ARRIETA	MORA			CAJICA	APOSENTO ALTO CAJICA	2025-03-13 13:01:29.20625	\N	\N	\N
136	22	00000	74135	Jesus		Roa		0	aaaaa@hotmail.com	0	CASA DE ORACIÓN	2025-08-26 21:40:54.028162	Soltero	Ninguno	0
10	20		000010	SARAI	ALEJANDRA	RICO	PEDRAZA			CAJICÁ	APOSENTO ALTO CAJI	2025-03-13 13:39:57.532816	\N	\N	\N
13	20		000013	ISABELLA		CAICEDO				CAJICA	APOSENTO ALTO CAJICA	2025-03-13 15:31:24.141686	\N	\N	\N
14	23	1010969455	000014	Belén	Anahi	Sánchez	Florez	3159271739		Bogotá	Iglesia Bautista el Camino	2025-03-19 16:30:13.478255	\N	\N	\N
15	23	1047508368	000015	Susana 		Herrera 				Bogotá 	Iglesia Bautista el Camino	2025-03-20 09:55:50.960944	\N	\N	\N
16	23	1141130510	000016	Abigail	Elizabeth	Sánchez	Florez	3158919435		Bogotá	Iglesia Bautista el Camino	2025-03-20 10:01:11.423002	\N	\N	\N
17	23		000017	Christian	Daniel	Lara	Sandoval	3054015178		Bogotá 	Iglesia Bautista el Camino	2025-03-20 10:58:19.717014	\N	\N	\N
18	24	11111111	0617	Angel	Gabriel	Osorio	xx	0	aaaaa@hotmail.com	La Mesa	xx	2025-06-12 16:05:11.29722	Soltero	Primaria	xx
19	24	1111111	0618	Maylin		Robledo		0	aaaaa@hotmail.com	La Mesa	xx	2025-06-12 16:09:26.90885	Soltero	Primaria	xx
20	24	11111111	0619	Marilu		Morales		0	aaaaa@hotmail.com	La Mesa	xx	2025-06-12 16:12:23.600342	Soltero	Primaria	xx
21	24	1111111	0620	Santiago		Benavides		0	aaaaa@hotmail.com	La Mesa	xx	2025-06-12 16:13:19.862878	Soltero	Primaria	xx
22	24	1111111	0621	Marta	Amparo	Loaiza		0	aaaaa@hotmail.com	La Mesa	xx	2025-06-12 16:14:23.765021	Soltero	Primaria	xx
23	24	11111111	0622	Esmeralda		Moreno		0	aaaaa@hotmail.com	La Mesa	xx	2025-06-12 16:15:17.739324	Soltero	Primaria	xx
24	24	1111111	0623	Marco	Emilio	Moreno		0	aaaaa@hotmail.com	La Mesa	xx	2025-06-12 16:16:25.630594	Soltero	Primaria	xx
25	1	111111	0724	Jose	Luis	Beltran		0	aaaaa@hotmail.com	Bogotá	xx	2025-06-12 16:20:51.294818	Soltero	Primaria	xx
26	1	111111	0725	Piedad		Ivonne		0	aaaaa@hotmail.com	Bogotá	xx	2025-06-12 16:22:46.901981	Soltero	Primaria	xx
27	1	2222222	0726	Harley	Davinson	Valdez		0	aaaaa@hotmail.com	Bogotá	xx	2025-06-12 16:28:40.522441	Soltero	Primaria	xx
28	1	111111	0727	Jose	Francisco	Jimenez		0	aaaaa@hotmail.com	Bogotá	xx	2025-06-12 16:29:39.418913	Soltero	Primaria	xx
29	1	1111111	0728	Omar		Barreto		0	aaaaa@hotmail.com	Bogotá	xx	2025-06-12 16:32:01.656702	Soltero	Primaria	xx
30	1	1111111	0729	Mauricio		Rodriguez		0	aaaaa@hotmail.com	Bogotá	xx	2025-06-12 16:33:47.991766	Soltero	Primaria	xx
31	1	1111111	0730	Marco	Emilo	Romero		0	aaaaa@hotmail.com	Bogotá	xx	2025-06-12 16:35:04.69424	Soltero	Primaria	xx
32	1	1111111	0731	Julio	Cesar	Posada	Terrada	0	aaaaa@hotmail.com	Bogotá	xx	2025-06-12 16:36:10.906007	Soltero	Primaria	xx
33	1	1111111	0732	Alexander		Gaitan		0	aaaaa@hotmail.com	Bogotá	xx	2025-06-12 16:37:27.528848	Soltero	Primaria	xx
34	4	1111111	0933	Jorge	Enrrique	Enrrique		0	aaaaa@hotmail.com	Bogotá	xx	2025-06-12 16:40:39.67834	Soltero	Primaria	xx
35	4	11111111	0934	Carlos		Galindo		0	aaaaa@hotmail.com	Bogotá	xx	2025-06-12 16:41:28.80753	Soltero	Primaria	xx
36	4	1111111	0935	Jose	Alejandro	Alejendro		0	aaaaa@hotmail.com	Bogotá	xx	2025-06-12 16:42:45.026919	Soltero	Primaria	xx
37	4	1111111	0936	Gustavo		Amaya		0	aaaaa@hotmail.com	Bogotá	xx	2025-06-12 16:43:53.755933	Soltero	Primaria	xx
38	4	111111111	0937	Fredy	Edilverto	Edilverto		0	aaaaa@hotmail.com	Bogotá	xx	2025-06-12 16:48:18.570699	Soltero	Primaria	xx
39	4	1111111	0938	Virgilio		Virgilio		0	aaaaa@hotmail.com	Bogotá	xx	2025-06-12 16:50:36.163534	Soltero	Primaria	xx
40	4	1111111	0939	Henrry		Henrry		0	aaaaa@hotmail.com	Bogotá	xxx	2025-06-12 16:52:04.862476	Soltero	Primaria	xx
41	4	1111111	0940	Jairo		Leon			aaaaa@hotmail.com	Bogotá	xxx	2025-06-12 16:55:39.02267	Soltero	Primaria	xx
55	6	1	1854	Leslie		Bernal		0	aaaaa@hotmail.com	BOGOTÁ	APOSENTO ALTO JJ VARGAS	2025-06-16 16:37:45.639215	Soltero	Secundaria	ESTUDIANTE
53	6	1	1852	Mariana		Buenaventura		0	aaaaa@hotmail.com	BOGOTÁ	APOSENTO ALTO JJ VARGAS	2025-06-16 16:36:25.158412	Soltero	Secundaria	ESTUDIANTE
51	6	1	1850	Esteban		Díaz	Montaño	0	aaaaa@hotmail.com	B	APOSENTO ALTO JJ VARGAS	2025-06-16 16:35:24.663937	Soltero	Secundaria	ESTUDIANTE
52	6	1	1851	Jonathan		Díaz	Montaño	0	aaaaa@hotmail.com	BOGOTÁ	APOSENTO ALTO JJ VARGAS	2025-06-16 16:36:00.028017	Soltero	Secundaria	ESTUDIANTE
57	6	1	1856	Martha	Cecilia	Díaz	Porras	0	aaaaa@hotmail.com	B	APOSENTO ALTO JJ VARGAS	2025-06-16 16:38:57.056731	Soltero	Secundaria	0
49	6	1	1848	Katherin		Díaz	Cruz	0	aaaaa@hotmail.com	BOGOTÁ	APOSENTO ALTO JJ VARGAS	2025-06-16 16:32:55.948045	Soltero	Secundaria	ESTUDIANTE
50	6	1	1849	Edgar 		Dussan		0	aaaaa@hotmail.com	BOGOTÁ	APOSENTO ALTO JJ VARGAS	2025-06-16 16:34:48.988288	Casado	Técnico	INDEPENDIENTE
46	6	1	1845	Doris		Espitia		0	aaaaa@hotmail.com	BOGOTÁ	APOSENTO ALTO JJ VARGAS	2025-06-16 16:30:48.231291	Casado	Ninguno	0
42	6	1	1841	Emmanuel		Fandiño	Pirabaguen	0	aaaaa@hotmail.com	BOGOTÁ	APOSENTO ALTO JJ VARGAS	2025-06-16 16:27:34.195915	Soltero	Secundaria	ESTUDIANTE
45	6	1	1844	Isabella		Fandiño	Pirabaguen	0	aaaaa@hotmail.com	BOGOTÁ	APOSENTO ALTO JJ VARGAS	2025-06-16 16:29:52.066896	Soltero	Universitario	ESTUDIANTE
58	6	1011205392	1857	Laura	Victoria	Saavedra	Garzón	3224775054	vickisaavedra495@gmail.com	BOGOTÁ	APOSENTO ALTO JJ VARGAS	2025-06-16 16:41:29.687447	Soltero	Secundaria	ESTUDIANTE
54	6	1	1853	Nicolas		Vanegas	Díaz	0	aaaaa@hotmail.com	BOGOTÁ	APOSENTO ALTO JJ VARGAS	2025-06-16 16:36:54.188427	Soltero	Primaria	ESTUDIANTE
47	6	1	1846	Jose	Daniel	Vanegas	Díaz	0	aaaaa@hotmail.com	BOGOTÁ	APOSENTO ALTO JJ VARGAS	2025-06-16 16:31:36.463478	Soltero	Secundaria	ESTUDIANTE
43	6	1	1842	Miguel		Fandiño	Pirabaguen	0	aaaaa@hotmail.com	BOGOTÁ	APOSENTO ALTO JJ VARGAS	2025-06-16 16:28:26.170689	Soltero	Primaria	ESTUDIANTE
44	6	1	1843	Sara	Valeria	Mendoza		0	aaaaa@hotmail.com	BOGOTÁ	APOSENTO ALTO JJ VARGAS	2025-06-16 16:29:13.117373	Soltero	Primaria	ESTUDIANTE
56	6	1	1855	Isabella		Nieto		0	aaaaa@hotmail.com	B	A	2025-06-16 16:38:17.459628	Soltero	Primaria	ESTUDIANTE
3	24		000003	Luis 	Ernesto	Silva	Beltran			Bogotá 	Centro Gaviotas	2024-12-11 17:19:00.972827	\N	\N	\N
11	20	1013025872	000011	JOSUE		RAMIREZ	VANEGAS	3045308163	samilenavanegasa113@gmail.com	CAJICA	APOSENTO ALTO CAJICA	2025-03-13 15:19:05.919733	\N	\N	\N
12	20	1013025873	000012	SARA		RAMIREZ	VANEGAS 	3045308163	samilenavanegasa113@gmail.com	CAJICÁ	APOSENTO ALTO CAJICA	2025-03-13 15:26:02.644312	\N	\N	\N
59	6	1	1858	Luis	David	Páez	Silva	0	aaaaa@hotmail.com	BOGOTÁ	APOSENTO ALTO JJ VARGAS	2025-06-16 17:50:12.207433	Casado	Universitario	INDEPENDIENTE
48	6	1	1847	Alan		Araque	Canty	0	aaaaa@hotmail.com	BOGOTÁ	APOSENTO ALTO JJ VARGAS	2025-06-16 16:32:18.84064	Soltero	Secundaria	ESTUDIANTE
60	2	1111111111	5859	Josue		Castañeda	Caballero	3000000000	aaaaa@hotmail.com	Chia	Bautista la Gracia Chia	2025-07-24 10:58:16.173761	Soltero	Secundaria	xx
61	2	1111111111	5860	Jony	Esteban	Linares	Malaver	3000000000	aaaaa@hotmail.com	Chia	Bautista la Gracia Chia	2025-07-24 11:08:51.189996	Soltero	Secundaria	xx
62	2	1111111111	5861	Josue		Díaz	Arebalo	3000000000	aaaaa@hotmail.com	Chia	Bautista la Gracia Chia	2025-07-24 11:10:16.027304	Soltero	Primaria	xx
63	2	1111111111	5862	Mariana		Barrera	Malaver	3000000000	aaaaa@hotmail.com	Chia	Bautista la Gracia Chia	2025-07-24 11:12:52.525787	Soltero	Primaria	xx
64	2	1111111111	5863	Salome		Bolaños	Buitrago	3000000000	aaaaa@hotmail.com	Chia	Bautista la Gracia Chia	2025-07-24 11:13:49.073009	Soltero	Primaria	xx
65	2	1111111111	5864	Samuel	Esteban	Duran	Lopez	3000000000	aaaaa@hotmail.com	Chia	Bautista la Gracia Chia	2025-07-24 11:15:37.404138	Soltero	Primaria	xx
66	2	1111111111	5865	Valery		Serrato	Florez	3000000000	aaaaa@hotmail.com	Chia	Bautista la Gracia Chia	2025-07-24 11:16:54.917928	Soltero	Primaria	xx
68	2	1111111111	5867	Samuel		Pabon		3000000000	aaaaa@hotmail.com	Chia	Bautista la Gracia Chia	2025-07-24 11:19:38.645331	Soltero	Primaria	xx
69	2	1111111111	5868	Matias		Díaz		3000000000	aaaaa@hotmail.com	Chia	Bautista la Gracia Chia	2025-07-24 11:20:29.148437	Soltero	Primaria	xx
70	2	1111111111	5869	Joaquin	Joel	Rengifo		3000000000	aaaaa@hotmail.com	Chia	Bautista la Gracia Chia	2025-07-24 11:21:47.5725	Soltero	Primaria	xx
96	3	1111111111	0895	Wilson	Miguel	Contreras		3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:25:08.908447	Soltero	Primaria	xx
97	3	1111111111	0896	Jhon	Pablo	Estupiñan	Baez	3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:26:45.12267	Soltero	Primaria	xx
98	3	1111111111	0897	Carlos	Julio	Meldivelso		3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:28:28.711304	Soltero	Primaria	xx
71	2	1013018138	5870	Juan	David	Bernal	Rubiano	3123791637	bernal.fredy@gmail.com	Cota	Bautista la Gracia Chia	2025-07-24 11:24:53.156477	Soltero	Primaria	xx
67	2	1111111111	5866	Juan	Luis	Pabon		3000000000	aaaaa@hotmail.com	Chia	Bautista la Gracia Chia	2025-07-24 11:18:31.34924	Soltero	Primaria	xx
72	2	1013025495	5871	Santiago		Bernal	Rubiano	3123791637	bernal.fredy@gmail.com	Cota	Bautista la Gracia Chia	2025-07-24 11:25:53.36384	Soltero	Primaria	xx
9	20	1013018452	000009	ANA	LUCIA 	RAMIREZ	VANEGAS	3045308163	samilenavanegasa113@gmail.com	CAJICÁ	APOSENTO ALTO CAJICA	2025-03-13 13:07:59.441558	\N	\N	\N
73	6	1025	1872	DAVID	ALEJANDRO	SALCEDO	APONTE	3014065419		BOGOTA	A.A. J.J. VARGAS	2025-08-21 18:41:15.082395	Soltero	Primaria	ESTUDIANTE
74	6	1013126570	1873	SARAY		MENDEZ	TOLOZA	3150599672		BOGOTA	A.A. J.J. VARGAS	2025-08-21 18:43:19.64976	Soltero	Primaria	ESTUDIANTE
75	4	1111111111	0974	Victor		Muñoz	Perez	3000000000	aaaaa@hotmail.com	Bogotá	Camino	2025-08-22 16:16:15.427787	Soltero	Primaria	xx
76	4	1111111111	0975	Carlos	Alberto	Trujillo		3000000000	aaaaa@hotmail.com	Bogotá	Camino	2025-08-22 16:33:29.38424	Soltero	Primaria	xx
77	4	1111111111	0976	Pedro	Pablo	Figueredo		3000000000	aaaaa@hotmail.com	Bogotá	Camino	2025-08-22 16:34:26.068529	Soltero	Primaria	xx
78	4	1111111111	0977	Carlos	Mauricio	Cubidez		3000000000	aaaaa@hotmail.com	Bogotá	Camino	2025-08-22 16:35:39.619517	Soltero	Primaria	xx
79	4	1111111111	0978	Juan	Carlos	Leiva		3000000000	aaaaa@hotmail.com	Bogotá	Camino	2025-08-22 16:37:12.471333	Soltero	Primaria	xx
80	4	1111111111	0979	Felix	Enrrique	Dominguez	Ballesteros	3000000000	aaaaa@hotmail.com	Bogotá	Camino	2025-08-22 16:38:41.76997	Soltero	Primaria	xx
81	4	1111111111	0980	Edgar		Pinzón	Rendón	3000000000	aaaaa@hotmail.com	Bogotá	Camino	2025-08-22 16:41:59.904199	Soltero	Primaria	xx
82	4	1111111111	0981	Jose	Oscar	Barbosa	Amaya	3000000000	aaaaa@hotmail.com	Bogotá	Camino	2025-08-22 16:43:41.634515	Soltero	Primaria	xx
83	4	1111111111	0982	Didier		Granados	Parra	3000000000	aaaaa@hotmail.com	Bogotá	Camino	2025-08-22 16:46:41.61201	Soltero	Primaria	xx
84	4	1111111111	0983	Jhon	Jairo	xx		3000000000	aaaaa@hotmail.com	Bogotá	Camino	2025-08-22 16:47:50.503666	Soltero	Primaria	xx
85	3	1111111111	0884	Luis	Eduardo	Ortiz		3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:00:52.879532	Soltero	Primaria	xx
86	3	1111111111	0885	Luis		Mancera		3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:01:52.361834	Soltero	Primaria	xx
87	3	1111111111	0886	William		Gonzalez		3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:04:58.434202	Soltero	Primaria	xx
88	3	1111111111	0887	Jesús	Antonio	Sanchez		3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:07:13.059298	Soltero	Primaria	xx
89	3	1111111111	0888	Mario	Antonio	Vanegas		3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:09:39.158407	Soltero	Primaria	xx
90	3	1111111111	0889	Cristian		Gutierrez		3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:14:16.101506	Soltero	Primaria	xx
91	3	1111111111	0890	Jorge		Ariza		3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:15:21.204035	Soltero	Secundaria	xx
92	6	1111111111	1891	Jose	Antonio	Ortegón		3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:16:55.929744	Soltero	Primaria	xx
93	3	1111111111	0892	Felix	Antonio	Sierra	Vargas	3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:21:09.617039	Soltero	Primaria	xx
94	3	1111111111	0893	Jorge	Andres	Mendoza	Zuica	3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:22:28.204771	Soltero	Primaria	xx
95	3	1111111111	0894	Mauricio	David	Perez	Acuña	3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:23:53.336474	Soltero	Primaria	xx
99	3	1111111111	0898	Giovanny	Mauricio	Soto		3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:29:44.443413	Soltero	Primaria	xx
100	3	1111111111	0899	Carlos		Arrieta	H	3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:31:10.464441	Soltero	Secundaria	xx
101	3	1111111111	08100	Victor	Manuel	Ruiz	Ramirez	3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:32:46.524242	Soltero	Primaria	xx
102	3	1111111111	08101	Yuly	Carolina	Romero		3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:36:41.767069	Soltero	Secundaria	xx
103	3	1111111111	08102	Jhony		Byacho		3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:37:52.205668	Soltero	Secundaria	xx
104	3	1111111111	08103	Jorge	Enrrique	Forero		3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:39:18.976471	Soltero	Secundaria	xx
105	3	1111111111	08104	Jose	Ariel	Florez	Sanchez	3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:41:18.033035	Soltero	Primaria	xx
106	3	1111111111	08105	Cesar	Alfredo	Alvarez		3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:42:36.3105	Soltero	Primaria	xx
107	3	1111111111	08106	Jorge		Rubio		3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:43:39.839106	Soltero	Primaria	xx
108	3	1111111111	08107	Omar 		Pantuja	Rivera	3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:44:47.320175	Soltero	Primaria	xx
109	3	1111111111	08108	Victor		Paez		3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:46:31.630914	Soltero	Primaria	xx
110	3	1111111111	08109	Alonso		Tejada		3000000000	aaaaa@hotmail.com	Bogotá	C.D.R	2025-08-22 19:52:12.519119	Soltero	Primaria	xx
111	5	1111111111	11110	Danna	Michelle	Chavez		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto la Vid	2025-08-26 13:29:55.929217	Soltero	Primaria	xx
112	5	1111111111	11111	Luciana		Caraballo	Vargas	3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto la Vid	2025-08-26 16:24:09.518675	Soltero	Primaria	xx
113	5	1111111111	11112	Ana	Katalina	Puentes	Perez	3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto la Vid	2025-08-26 16:25:16.518558	Soltero	Primaria	xx
114	9	0000000000	105113	Luciana		Clavijo		0	aaaaa@hotmail.com	0	GIMNASIO EL SHADDAI	2025-08-26 20:57:44.39706	Soltero	Primaria	ESTUDIANTE
115	9	00000	105114	Gabriela		Lopez		0	aaaaa@hotmail.com	BOGOTÁ	GIMNASIO EL SHADDAI	2025-08-26 21:07:28.107502	Soltero	Primaria	ESTUDIANTE
116	9	00000	105115	Zaray		Carranza	Forero	0	aaaaa@hotmail.com	BOGOTÁ	GIMNASIO EL SHADDAI	2025-08-26 21:08:23.636148	Soltero	Primaria	ESTUDIANTE
117	9	00000	105116	David		Sanchez		0	aaaaa@hotmail.com	BOGOTÁ	GIMNASIO EL SHADDAI	2025-08-26 21:09:10.491919	Soltero	Primaria	ESTUDIANTE
118	9	00000	105117	Matias		Vanegas		0	aaaaa@hotmail.com	BOGOTÁ	GIMNASIO EL SHADDAI	2025-08-26 21:10:17.609016	Soltero	Primaria	ESTUDIANTE
119	9	00000	105118	Juanita		Sanchez	Bohorquez	0	aaaaa@hotmail.com	BOGOTÁ	GIMNASIO EL SHADDAI	2025-08-26 21:12:49.326519	Soltero	Primaria	ESTUDIANTE
120	9	00000	105119	Juana		Rubio	Marago	0	aaaaa@hotmail.com	BOGOTÁ	GIMNASIO EL SHADDAI	2025-08-26 21:13:22.196077	Soltero	Primaria	ESTUDIANTE
121	9	00000	105120	Samuel		Contreras		0	aaaaa@hotmail.com	BOGOTÁ	GIMNASIO EL SHADDAI	2025-08-26 21:14:01.759539	Soltero	Primaria	ESTUDIANTE
122	9	00000	105121	Emmanuel	Francisco	Rojas		0	aaaaa@hotmail.com	BOGOTÁ	GIMNASIO EL SHADDAI	2025-08-26 21:14:32.755619	Soltero	Primaria	ESTUDIANTE
123	9	00000	105122	Isabella		Maya		0	aaaaa@hotmail.com	BOGOTÁ	GIMNASIO EL SHADDAI	2025-08-26 21:15:05.594499	Soltero	Primaria	ESTUDIANTE
124	9	00000	105123	Sara	Valentina	Alarcon		0	aaaaa@hotmail.com	BOGOTÁ	GIMNASIO EL SHADDAI	2025-08-26 21:15:41.295289	Soltero	Primaria	ESTUDIANTE
125	9	00000	105124	Kevin	Santiago	Toloza		0	aaaaa@hotmail.com	BOGOTÁ	GIMNASIO EL SHADDAI	2025-08-26 21:16:17.646676	Soltero	Primaria	ESTUDIANTE
126	9	00000	105125	Taylor	Felipe	Sanchez	Espitia	0	aaaaa@hotmail.com	BOGOTÁ	GIMNASIO EL SHADDAI	2025-08-26 21:16:59.680295	Soltero	Primaria	ESTUDIANTE
127	9	00000	105126	Jhon	Carlos	Mixael		0	aaaaa@hotmail.com	BOGOTÁ	GIMNASIO EL SHADDAI	2025-08-26 21:17:42.904419	Soltero	Primaria	ESTUDIANTE
128	9	00000	105127	Manuel		Zuluaga		0	aaaaa@hotmail.com	BOGOTÁ	GIMNASIO EL SHADDAI	2025-08-26 21:22:53.103898	Soltero	Primaria	ESTUDIANTE
129	9	00000	105128	Miguel		Serrano		0	aaaaa@hotmail.com	BOGOTÁ	GIMNASIO EL SHADDAI	2025-08-26 21:23:31.201018	Soltero	Primaria	ESTUDIANTE
130	9	00000	105129	Joshua		Salinas		0	aaaaa@hotmail.com	BOGOTÁ	GIMNASIO EL SHADDAI	2025-08-26 21:24:44.754758	Soltero	Primaria	ESTUDIANTE
131	9	00000	105130	David		Eduardo		0	aaaaa@hotmail.com	BOGOTÁ	GIMNASIO EL SHADDAI	2025-08-26 21:25:21.957156	Soltero	Primaria	ESTUDIANTE
132	9	00000	105131	Valentina		Pinto		0	aaaaa@hotmail.com	BOGOTÁ	GIMNASIO EL SHADDAI	2025-08-26 21:25:51.788138	Soltero	Primaria	ESTUDIANTE
133	9	00000	105132	Caleb		Match	Monroy	0	aaaaa@hotmail.com	BOGOTÁ	GIMNASIO EL SHADDAI	2025-08-26 21:26:23.487528	Soltero	Primaria	ESTUDIANTE
134	13	00000	37133	Jeronimo		Montoya		0	aaaaa@hotmail.com	BOGOTÁ	APOSENTO ALTO MUZÚ	2025-08-26 21:31:06.197241	Soltero	Primaria	ESTUDIANTE
135	22	00000	74134	Victoria	Elisabeth	Roa	Sierra	0	aaaaa@hotmail.com	0	CASA DE ORACIÓN	2025-08-26 21:36:26.047927	Soltero	Primaria	0
137	22	00000	74136	Rocio		Alvarez	Alvarez	0	aaaaa@hotmail.com	0	CASA DE ORACIÓN	2025-08-26 21:41:43.947072	Soltero	Secundaria	0
138	22	00000	74137	Alexa	Jineth	Pineda		0	aaaaa@hotmail.com	0	CASA DE ORACIÓN	2025-08-26 21:42:21.223098	Soltero	Ninguno	0
139	22	00000	74138	Isabella		Fajardo		0	aaaaa@hotmail.com	0	CASA DE ORACIÓN	2025-08-26 21:42:57.84301	Soltero	Ninguno	0
140	22	00000	74139	Andrea		Moreno		0	aaaaa@hotmail.com	0	CASA DE ORACIÓN	2025-08-26 21:43:28.771824	Soltero	Secundaria	0
141	22	00000	74140	Blanca	Maria	Bautista		0	aaaaa@hotmail.com	0	CASA DE ORACIÓN	2025-08-26 21:44:06.802235	Soltero	Ninguno	0
142	22	00000	74141	Rosa		Ardila		0	aaaaa@hotmail.com	0	CASA DE ORACIÓN	2025-08-26 21:44:44.55917	Soltero	Secundaria	0
143	22	00000	74142	Laura	Natalia	Bustos	Herrera	0	aaaaa@hotmail.com	0	CASA DE ORACIÓN	2025-08-26 21:45:22.512426	Soltero	Secundaria	0
144	22	00000	74143	Angel	Fredy	Forero	Paez	0	aaaaa@hotmail.com	0	CASA DE ORACIÓN	2025-08-26 21:46:05.034147	Soltero	Ninguno	0
145	22	00000	74144	Luna		Gomez	Huertas	0	aaaaa@hotmail.com	0	CASA DE ORACIÓN	2025-08-26 21:46:48.886522	Soltero	Ninguno	0
146	22	00000	74145	Mirley		Gonzalez	Plaza	0	aaaaa@hotmail.com	0	CASA DE ORACIÓN	2025-08-26 21:47:24.946995	Soltero	Ninguno	0
147	22	00000	74146	Sandra		Narvaez		0	aaaaa@hotmail.com	0	CASA DE ORACIÓN	2025-08-26 21:48:07.328118	Soltero	Ninguno	0
148	22	000000	74147	Alexandra		Arcos	Uribe	0	aaaaa@hotmail.com	0	CASA DE ORACIÓN	2025-08-26 21:48:51.630329	Soltero	Secundaria	0
149	22	00000	74148	Luciana	Camila	Manta	Beltran	0	aaaaa@hotmail.com	0	CASA DE ORACIÓN	2025-08-26 21:49:33.689494	Soltero	Ninguno	0
150	22	00000	74149	Lucy		Beltran	Ruiz	0	aaaaa@hotmail.com	0	CASA DE ORACIÓN	2025-08-26 21:50:14.036025	Soltero	Ninguno	0
151	22	00000	74150	Eliecer		Vargas		0	aaaaa@hotmail.com	0	CASA DE ORACIÓN	2025-08-26 21:50:51.483166	Soltero	Ninguno	0
152	22	00000	74151	Flor	Estrella	Rodriguez	Rodriguez	0	aaaaa@hotmail.com	0	CASA DE ORACIÓN	2025-08-26 21:51:31.738003	Soltero	Ninguno	0
153	22	00000	74152	Leidy	Yohana	Garcia	Romero	0	aaaaa@hotmail.com	0	CASA DE ORACIÓN	2025-08-26 21:52:07.790074	Soltero	Ninguno	0
154	22	0000	74153	David		Vidal	Velasquez	0	aaaaa@hotmail.com	0	CASA DE ORACIÓN	2025-08-26 21:52:34.96783	Soltero	Ninguno	0
155	22	0000	74154	Maria	Ruberta	Ruda	Caceres	0	aaaaa@hotmail.com	0	CASA DE ORACIÓN	2025-08-26 21:53:14.993461	Soltero	Ninguno	0
156	22	00000	74155	Margoth		Murillo		0	aaaaa@hotmail.com	0	CASA DE ORACIÓN	2025-08-26 21:53:47.217722	Soltero	Ninguno	0
157	10	1111111111	28156	Sandra	Milena	Centeno		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 12:28:09.410589	Soltero	Primaria	xx
158	10	1111111111	28157	Diana	Carolina	Centeno		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 12:32:03.839042	Soltero	Primaria	xx
159	10	1111111111	28158	Magnolia		Lopez		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 12:33:01.997453	Soltero	Primaria	xx
160	10	1111111111	28159	Maria		Saray		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 12:34:04.475827	Soltero	Primaria	xx
161	10	1111111111	28160	Julieth	Dayana	Estupiñan		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 12:35:35.47297	Soltero	Primaria	xx
162	10	1111111111	28161	Karen	Sofia	León	Lara	3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 12:37:31.291233	Soltero	Primaria	xx
163	10	1111111111	28162	Alexander		León	Lara	3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 12:38:48.848886	Soltero	Primaria	xx
165	10	1111111111	28164	Johan		Rubiano		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 12:53:10.136	Soltero	Primaria	xx
166	10	1111111111	28165	Vicent	Adrian	Giraldo	Florez	3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 12:56:07.900259	Soltero	Primaria	xx
167	10	1111111111	28166	Roxana		Flores		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 12:57:27.600635	Soltero	Primaria	xx
168	10	1111111111	28167	Abrahan		Bautte		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 12:58:40.091763	Soltero	Primaria	xx
169	10	1111111111	28168	Victor		xx		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 12:59:52.183622	Soltero	Primaria	xx
170	10	1111111111	28169	Jorge		Fernandez		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 13:30:11.94341	Soltero	Primaria	xx
171	10	1111111111	28170	NAIONY	SAHANA	Pabón		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 13:32:16.202695	Soltero	Primaria	xx
172	10	1111111111	28171	Miguel	Angel	Soto		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 13:43:53.900524	Soltero	Primaria	xx
173	10	1111111111	28172	Sharol		Hernandez		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 13:45:32.107547	Soltero	Primaria	xx
174	10	1111111111	28173	Tatiana		Riscanevo		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 13:46:51.374939	Soltero	Primaria	xx
175	10	1111111111	28174	Violeth		Hernandez		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 13:48:27.810235	Soltero	Primaria	xx
176	10	1111111111	28175	Andres	David	Benavides		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 13:49:50.041927	Soltero	Primaria	xx
177	10	1111111111	28176	Gisel	Samanta	Benavides		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 13:51:16.738952	Soltero	Primaria	xx
178	10	1111111111	28177	Ian	Felipe	Mesa	Higuera	3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 13:52:38.592728	Soltero	Primaria	xx
179	10	1111111111	28178	Derly	Alejandra	Bautista	Cortes	3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 13:53:56.197431	Soltero	Primaria	xx
180	10	1111111111	28179	Chantal		xx		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 13:54:58.731647	Soltero	Primaria	xx
181	10	1111111111	28180	Genesis		xx		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 13:56:02.450733	Soltero	Primaria	xx
164	10	1111111111	28163	Laura	Estefania	Rodriguez	Vargas	3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Berlin	2025-08-28 12:41:12.988005	Soltero	Primaria	xx
182	7	1111111111	22181	Ana		Sanabria		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Venecia	2025-09-04 15:48:48.521176	Soltero	Primaria	xx
183	8	1111111111	23182	Maria	Juliana	Rodriguez		3000000000	aaaaa@hotmail.com	Bogotá	Aposento alto Suba	2025-09-04 16:04:38.28655	Soltero	Primaria	xx
184	8	1111111111	23183	Valeria		Rodriguez	Gualteros	3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Suba	2025-09-04 16:05:44.345722	Soltero	Primaria	xx
185	11	1111111111	30184	Tatiana		Rodriguez		3000000000	aaaaa@hotmail.com	Bogotá	Local Biblico  La Alqueria	2025-09-04 16:38:38.420842	Casado	Universitario	xx
186	11	1111111111	30185	Angie		Cera		3000000000	aaaaa@hotmail.com	Bogotá	Local Biblico Alqueria	2025-09-04 16:39:56.190481	Soltero	Primaria	xx
187	11	1111111111	30186	Luisa		Guzman		3000000000	aaaaa@hotmail.com	Bogotá	Alqueria	2025-09-04 16:45:36.273261	Soltero	Primaria	xx
188	11	1111111111	30187	Samuel		Camelo		3000000000	aaaaa@hotmail.com	Bogotá	Alqueria	2025-09-04 16:46:37.732397	Soltero	Secundaria	xx
189	11	1111111111	30188	Andrea		Segovia		3000000000	aaaaa@hotmail.com	Bogotá	Alqueria	2025-09-04 16:47:41.212005	Soltero	Primaria	xx
190	11	1111111111	30189	Jean	Stiven	Crespo		3000000000	aaaaa@hotmail.com	Bogotá	Alqueria	2025-09-04 16:48:51.388863	Soltero	Primaria	xx
191	11	1111111111	30190	David		Cera		3000000000	aaaaa@hotmail.com	Bogotá	Alqueria	2025-09-04 16:51:34.256022	Soltero	Primaria	xx
192	11	1111111111	30191	Hikmar		Valdivieso		3000000000	aaaaa@hotmail.com	Bogotá	Alqueria	2025-09-04 16:54:07.765667	Soltero	Primaria	xx
193	12	1111111111	36192	Juan	José	Molina	Ricón	3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Orquideas	2025-09-09 16:20:31.05944	Soltero	Primaria	xx
194	12	1111111111	36193	Juan	Esteban	Rodríguez		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Orquideas	2025-09-09 16:27:07.750136	Soltero	Primaria	xx
195	12	1111111111	36194	Isabella		Hernández		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Orquideas	2025-09-09 16:28:19.129257	Soltero	Primaria	xx
196	12	1111111111	36195	Mariana		Guerra	Guerra	3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Orquideas	2025-09-09 16:30:02.420142	Soltero	Primaria	xx
197	16	1111111111	46196	María	Angelica	Rubiano	c	3000000000	aaaaa@hotmail.com	Bogotá	Comunidad de Crecimiento Cristiano	2025-09-11 13:43:30.544756	Soltero	Primaria	xx
198	16	1111111111	46197	Sidel	Alejandra	Duarte	Hernandez	3000000000	aaaaa@hotmail.com	Bogotá	Comunidad de Crecimiento Cristiano	2025-09-11 13:49:33.759042	Soltero	Primaria	xx
199	16	1111111111	46198	Samuel	David	Galindo	Parada	3000000000	aaaaa@hotmail.com	Bogotá	Comunidad de Crecimiento Cristiano	2025-09-11 13:51:30.785673	Soltero	Primaria	xx
200	16	1111111111	46199	Salvador		Rangel	Reyes	3000000000	aaaaa@hotmail.com	Bogotá	Comunidad de Crecimiento Cristiano	2025-09-11 13:54:25.99322	Soltero	Primaria	xx
201	16	1111111111	46200	Jesús	David	Puentes	Martinez	3000000000	aaaaa@hotmail.com	Bogotá	Comunidad de Crecimiento Cristiano	2025-09-11 13:55:35.672958	Soltero	Primaria	xx
202	16	1111111111	46201	Jader	Ricardo	Valbuena	Ortiz	3000000000	aaaaa@hotmail.com	Bogotá	Comunidad de Crecimiento Cristiano	2025-09-11 13:57:07.459591	Soltero	Primaria	xx
203	16	1111111111	46202	Samuel	Andrés	Rubiano	Chacon	3000000000	aaaaa@hotmail.com	Bogotá	Comunidad de Crecimiento Cristiano	2025-09-11 14:03:32.099388	Soltero	Primaria	xx
204	16	1111111111	46203	Marlly	Tatiana	Valbuena	Ortiz	3000000000	aaaaa@hotmail.com	Bogotá	Comunidad de Crecimiento Cristiano	2025-09-11 14:04:52.490138	Soltero	Primaria	xx
205	16	1111111111	46204	Samuel	Felipe	Arenas	Jaimes	3000000000	aaaaa@hotmail.com	Bogotá	Comunidad de Crecimiento Cristiano	2025-09-11 14:10:55.104022	Soltero	Primaria	xx
206	16	1111111111	46205	Kevin	Santiago	Delgado	S	3000000000	aaaaa@hotmail.com	Bogotá	Comunidad de Crecimiento Cristiano	2025-09-11 14:12:42.211236	Soltero	Primaria	xx
207	16	1111111111	46206	Fabian	Camilo	Rubio	Arenas	3000000000	aaaaa@hotmail.com	Bogotá	Comunidad de Crecimiento Cristiano	2025-09-11 14:14:02.832884	Soltero	Primaria	xx
208	16	1111111111	46207	María	Jose	Rodriguez	Flor	3000000000	aaaaa@hotmail.com	Bogotá	Comunidad de Crecimiento Cristiano	2025-09-11 14:15:28.91759	Soltero	Primaria	xx
209	16	1111111111	46208	Sara	Camila	Galindo	Abril	3000000000	aaaaa@hotmail.com	Bogotá	Comunidad de Crecimiento Cristiano	2025-09-11 14:16:51.893546	Soltero	Primaria	xx
210	16	1111111111	46209	David	Santiago	Lopez		3000000000	aaaaa@hotmail.com	Bogotá	Comunidad de Crecimiento Cristiano	2025-09-11 14:18:07.52146	Soltero	Primaria	xx
211	16	1111111111	46210	Nicoll		Delgado	S	3000000000	aaaaa@hotmail.com	Bogotá	Comunidad de Crecimiento Cristiano	2025-09-11 14:19:14.327166	Soltero	Primaria	xx
212	16	1111111111	46211	Cristopher	Shamuel	R	M	3000000000	aaaaa@hotmail.com	Bogotá	Comunidad de Crecimiento Cristiano	2025-09-11 14:21:52.061981	Soltero	Primaria	xx
213	16	1111111111	46212	Emanuel		Ramirez		3000000000	aaaaa@hotmail.com	Bogotá	Comunidad de Crecimiento Cristiano	2025-09-11 14:22:46.182748	Soltero	Secundaria	xx
214	16	1111111111	46213	Maick		Ramirez		3000000000	aaaaa@hotmail.com	Bogotá	Comunidad de Crecimiento Cristiano	2025-09-11 14:23:40.959256	Soltero	Primaria	xx
215	16	1111111111	46214	Joel	David	xx		3000000000	aaaaa@hotmail.com	Bogotá	Comunidad de Crecimiento Cristiano	2025-09-11 15:42:30.576096	Soltero	Primaria	xx
216	16	1111111111	46215	Samuel		Reyes		3000000000	aaaaa@hotmail.com	Bogotá	Comunidad de Crecimiento Cristiano	2025-09-11 15:43:30.182396	Soltero	Primaria	xx
217	16	1111111111	46216	Zafire		Mogollon		3000000000	aaaaa@hotmail.com	Bogotá	Comunidad de Crecimiento Cristiano	2025-09-11 15:44:15.940661	Soltero	Primaria	xx
218	16	1111111111	46217	Pedro	Pablo	Rangel		3000000000	aaaaa@hotmail.com	Bogotá	Comunidad de Crecimiento Cristiano	2025-09-11 15:45:22.05035	Soltero	Primaria	xx
219	18	1111111111	61218	Juan	Diego	Cruz		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Centro	2025-09-11 17:58:23.382916	Soltero	Primaria	xx
220	18	1111111111	61219	Valery	Alexandra	Pinzón		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Centro	2025-09-11 18:00:52.924866	Soltero	Primaria	xx
221	18	1111111111	61220	María	Camila	Gonzales		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Centro	2025-09-11 18:02:27.953805	Soltero	Primaria	xx
222	18	1111111111	61221	Valery	Samantha C	Chaves		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Centro	2025-09-11 18:03:49.751059	Soltero	Primaria	xx
223	18	1111111111	61222	Kaleth		Martinez		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Centro	2025-09-11 18:05:04.916797	Soltero	Primaria	xx
224	18	1111111111	61223	Angell	Mariana	Amaya	Beltran	3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Centro	2025-09-11 18:06:20.968901	Soltero	Primaria	xx
225	18	1111111111	61224	Danna	Sofia	Amaya	Beltran	3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Centro	2025-09-11 18:07:53.376109	Soltero	Primaria	xx
226	18	1111111111	61225	Matias		Arias		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Centro	2025-09-11 18:08:52.459592	Soltero	Primaria	xx
227	18	1111111111	61226	Alan	Santiago	xx	xx	3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Centro	2025-09-11 18:09:58.572698	Soltero	Primaria	xx
228	18	1111111111	61227	Alan	Santiago	xx	xx	3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Centro	2025-09-11 18:09:58.610648	Soltero	Primaria	xx
229	18	1111111111	61228	Mariana		Arias		3000000000	aaaaa@hotmail.com	Bogotá	Aposento Alto Centro	2025-09-11 18:10:50.085281	Soltero	Primaria	xx
230	1	1026594542	07229	LEOPOLDO		JIMENEZ	SALINAS	00000000000	mmmmmm@gmail.com	BOGOTÁ	BALCANES	2025-10-02 15:29:41.608432	Soltero	Secundaria	,
231	1	75081494	07230	JUAN	CARLOS	TORRES	ROJAS	00000000000	mmmmmm@gmail.com	BOGOTÁ	BALCANES	2025-10-02 15:31:49.12268	Soltero	Secundaria	VENDEDROR DE PLANTAS
\.


--
-- Data for Name: estudiantes_cursos; Type: TABLE DATA; Schema: public; Owner: emmaus
--

COPY estudiantes_cursos (id, estudiante_id, curso_id, fecha, porcentaje, enviado) FROM stdin;
1	1	26	2024-12-03	100	f
2	1	27	2024-12-03	100	f
3	1	28	2024-12-03	100	f
4	1	29	2024-12-03	100	f
5	1	30	2024-12-03	100	f
6	1	31	2024-12-03	100	f
7	1	32	2024-12-03	100	f
8	1	33	2024-12-03	100	f
9	1	34	2024-12-03	100	f
10	1	35	2024-12-03	100	f
11	1	36	2024-12-03	100	f
12	1	37	2024-12-03	100	f
13	1	39	2024-12-03	100	f
14	1	45	2024-12-03	90	f
15	1	41	2024-12-03	100	f
16	1	42	2024-12-03	100	f
17	2	55	2024-12-10	100	f
18	2	56	2024-12-11	86	f
19	2	57	2024-12-11	80	f
20	2	58	2024-12-11	80	f
21	2	59	2024-12-11	95	f
22	2	60	2024-12-11	80	f
23	2	62	2024-12-11	100	f
24	2	63	2024-12-11	95	f
25	2	64	2024-12-11	85	f
26	2	65	2024-12-11	95	f
27	2	73	2024-12-11	100	f
28	2	74	2024-12-11	90	f
29	2	75	2024-12-11	93	f
30	2	76	2024-12-11	98	f
31	2	77	2024-12-11	97	f
32	2	78	2024-12-11	95	f
33	2	95	2024-12-11	100	f
34	2	96	2024-12-11	100	f
35	2	97	2024-12-11	100	f
36	2	98	2024-12-11	100	f
37	2	99	2024-12-11	100	f
38	2	100	2024-12-11	100	f
39	2	101	2024-12-11	100	f
40	2	102	2024-12-11	100	f
41	2	103	2024-12-11	95	f
42	2	104	2024-12-11	95	f
43	2	105	2024-12-11	100	f
44	2	106	2024-12-11	97	f
45	2	107	2024-12-11	100	f
46	2	116	2024-12-11	90	f
47	2	117	2024-12-11	90	f
48	2	118	2024-12-11	100	f
49	2	119	2024-12-11	100	f
50	2	120	2024-12-11	98	f
51	2	121	2024-12-11	100	f
52	2	122	2024-12-11	100	f
53	2	123	2024-12-11	100	f
54	2	124	2024-12-11	100	f
55	2	125	2024-12-11	100	f
56	2	126	2024-12-11	100	f
57	2	127	2024-12-11	100	f
58	2	128	2024-12-11	95	f
59	2	129	2024-12-11	90	f
60	2	130	2024-12-11	95	f
61	2	134	2024-12-11	92	f
62	2	135	2024-12-11	86	f
63	2	138	2024-12-11	100	f
64	2	136	2024-12-11	72	f
65	2	137	2024-12-11	82	f
66	2	140	2024-12-11	89	f
67	3	55	2024-12-11	70	f
68	3	56	2024-12-11	100	f
69	3	57	2024-12-11	100	f
70	3	58	2024-12-11	100	f
71	3	59	2024-12-11	80	f
72	3	60	2024-12-11	100	f
73	3	61	2024-12-11	100	f
74	3	62	2024-12-11	85	f
75	3	63	2024-12-11	90	f
76	3	64	2024-12-11	80	f
77	3	65	2024-12-11	78	f
78	3	73	2024-12-11	70	f
79	3	74	2024-12-11	100	f
80	3	75	2024-12-11	100	f
81	3	76	2024-12-11	100	f
82	3	77	2024-12-11	100	f
83	3	78	2024-12-11	100	f
84	4	26	2025-03-13	91	f
85	4	30	2025-03-13	100	f
86	4	28	2025-03-13	100	f
87	4	29	2025-03-13	100	f
88	4	30	2025-03-13	100	f
89	4	31	2025-03-13	100	f
90	4	35	2025-03-13	100	f
91	4	33	2025-03-13	100	f
92	4	34	2025-03-13	100	f
93	4	35	2025-03-13	100	f
94	4	36	2025-03-13	100	f
95	4	37	2025-03-13	97	f
96	5	26	2025-03-13	91	f
97	5	30	2025-03-13	88	f
98	5	28	2025-03-13	83	f
99	5	29	2025-03-13	83	f
100	5	30	2025-03-13	83	f
101	6	26	2025-03-13	91	f
102	6	27	2025-03-13	88	f
103	6	28	2025-03-13	91	f
104	6	29	2025-03-13	100	f
105	6	30	2025-03-13	100	f
106	6	31	2025-03-13	100	f
107	7	26	2025-03-13	91	f
108	7	27	2025-03-13	83	f
109	7	28	2025-03-13	77	f
110	7	29	2025-03-13	83	f
111	7	30	2025-03-13	83	f
112	7	31	2025-03-13	83	f
113	7	29	2025-03-13	83	f
114	7	30	2025-03-13	83	f
115	7	31	2025-03-13	83	f
116	8	26	2025-03-13	83	f
117	8	27	2025-03-13	83	f
118	8	28	2025-03-13	77	f
119	7	29	2025-03-13	83	f
120	8	30	2025-03-13	83	f
121	8	32	2025-03-13	100	f
122	8	33	2025-03-13	100	f
123	8	34	2025-03-13	100	f
124	8	36	2025-03-13	100	f
125	8	36	2025-03-13	100	f
126	8	37	2025-03-13	100	f
127	9	26	2025-03-13	91	f
128	9	27	2025-03-13	88	f
129	9	28	2025-03-13	88	f
130	9	29	2025-03-13	100	f
131	9	30	2025-03-13	100	f
132	9	31	2025-03-13	88	f
133	9	32	2025-03-13	100	f
134	9	33	2025-03-13	100	f
135	9	34	2025-03-13	100	f
136	9	38	2025-03-13	100	f
137	9	39	2025-03-13	100	f
138	9	40	2025-03-13	100	f
139	9	41	2025-03-13	100	f
140	9	42	2025-03-13	100	f
141	9	43	2025-03-13	100	f
142	9	44	2025-03-13	100	f
143	9	43	2025-03-13	100	f
144	9	44	2025-03-13	100	f
145	9	45	2025-03-13	100	f
146	9	46	2025-03-13	100	f
147	10	26	2025-03-13	81	f
148	10	27	2025-03-13	83	f
149	10	28	2025-03-13	77	f
150	10	29	2025-03-13	83	f
151	10	30	2025-03-13	83	f
152	10	31	2025-03-13	77	f
153	10	32	2025-03-13	100	f
154	10	33	2025-03-13	100	f
155	10	34	2025-03-13	100	f
156	10	29	2025-03-13	100	f
157	10	36	2025-03-13	95	f
158	10	37	2025-03-13	75	f
159	10	29	2025-03-13	83	f
160	10	30	2025-03-13	83	f
161	10	31	2025-03-13	77	f
162	10	35	2025-03-13	100	f
163	10	36	2025-03-13	95	f
164	10	37	2025-03-13	75	f
165	11	26	2025-03-13	100	f
166	11	27	2025-03-13	100	f
167	11	28	2025-03-13	93	f
168	11	29	2025-03-13	100	f
169	11	30	2025-03-13	91	f
170	11	32	2025-03-13	100	f
171	12	26	2025-03-13	100	f
172	12	27	2025-03-13	100	f
173	12	29	2025-03-13	83	f
174	12	30	2025-03-13	83	f
175	12	32	2025-03-13	100	f
176	13	26	2025-03-13	80	f
177	13	27	2025-03-13	80	f
178	13	28	2025-03-13	80	f
179	13	30	2025-03-13	80	f
180	13	31	2025-03-13	73	f
181	7	38	2025-03-18	90	f
182	7	39	2025-03-18	100	f
183	7	40	2025-03-18	100	f
184	7	41	2025-03-18	100	f
185	7	42	2025-03-18	90	f
186	7	43	2025-03-18	80	f
187	13	32	2025-03-18	100	f
188	13	33	2025-03-18	100	f
189	13	29	2025-03-18	100	f
190	14	26	2025-03-20	100	f
191	14	27	2025-03-20	100	f
192	15	26	2025-03-20	100	f
193	15	27	2025-03-20	100	f
194	15	28	2025-03-20	100	f
195	16	26	2025-03-20	100	f
196	16	27	2025-03-20	100	f
197	16	28	2025-03-20	100	f
198	17	26	2025-03-20	100	f
199	17	27	2025-03-20	100	f
200	17	28	2025-03-20	100	f
201	42	26	2025-06-16	81	f
202	42	27	2025-06-16	83	f
203	42	28	2025-06-16	77	f
204	42	29	2025-06-16	83	f
205	42	30	2025-06-16	83	f
206	42	31	2025-06-16	71	f
207	42	32	2025-06-16	100	f
208	42	33	2025-06-16	100	f
209	42	34	2025-06-16	100	f
210	42	35	2025-06-16	100	f
211	42	36	2025-06-16	100	f
212	42	37	2025-06-16	100	f
213	43	26	2025-06-16	81	f
214	43	27	2025-06-16	83	f
215	43	28	2025-06-16	77	f
216	43	29	2025-06-16	83	f
217	43	30	2025-06-16	83	f
218	43	31	2025-06-16	83	f
219	43	32	2025-06-16	100	f
220	43	33	2025-06-16	100	f
221	43	34	2025-06-16	100	f
222	43	35	2025-06-16	100	f
223	43	36	2025-06-16	100	f
224	43	37	2025-06-16	100	f
225	44	26	2025-06-16	81	f
226	44	27	2025-06-16	66	f
227	44	28	2025-06-16	77	f
228	44	29	2025-06-16	83	f
229	44	30	2025-06-16	83	f
230	44	31	2025-06-16	83	f
231	44	32	2025-06-16	87	f
232	44	33	2025-06-16	100	f
233	44	34	2025-06-16	100	f
234	44	35	2025-06-16	87	f
235	44	36	2025-06-16	100	f
236	44	37	2025-06-16	100	f
237	44	38	2025-06-16	80	f
238	44	39	2025-06-16	100	f
239	44	40	2025-06-16	100	f
240	45	49	2025-06-16	95	f
241	45	50	2025-06-16	100	f
242	45	51	2025-06-16	100	f
243	45	52	2025-06-16	100	f
244	45	53	2025-06-16	95	f
245	45	54	2025-06-16	100	f
246	46	26	2025-06-16	100	f
247	46	27	2025-06-16	100	f
248	46	28	2025-06-16	100	f
249	46	29	2025-06-16	100	f
250	46	30	2025-06-16	100	f
251	46	31	2025-06-16	100	f
252	46	32	2025-06-16	100	f
253	46	33	2025-06-16	100	f
254	46	34	2025-06-16	100	f
255	46	36	2025-06-16	100	f
256	46	37	2025-06-16	100	f
257	46	38	2025-06-16	100	f
258	46	39	2025-06-16	100	f
259	46	40	2025-06-16	100	f
260	46	41	2025-06-16	100	f
261	46	42	2025-06-16	100	f
262	46	43	2025-06-16	100	f
263	46	44	2025-06-16	100	f
264	46	45	2025-06-16	100	f
265	46	46	2025-06-16	100	f
266	46	47	2025-06-16	100	f
267	46	48	2025-06-16	100	f
268	46	49	2025-06-16	100	f
269	46	50	2025-06-16	100	f
270	46	51	2025-06-16	100	f
271	46	52	2025-06-16	100	f
272	46	53	2025-06-16	100	f
273	46	54	2025-06-16	100	f
274	46	58	2025-06-16	100	f
275	46	59	2025-06-16	100	f
276	46	60	2025-06-16	100	f
277	46	61	2025-06-16	96	f
278	46	62	2025-06-16	100	f
279	46	63	2025-06-16	100	f
280	46	64	2025-06-16	95	f
281	46	65	2025-06-16	95	f
282	46	73	2025-06-16	100	f
283	46	74	2025-06-16	100	f
284	46	75	2025-06-16	100	f
285	46	76	2025-06-16	100	f
286	46	77	2025-06-16	100	f
287	46	78	2025-06-16	100	f
288	46	95	2025-06-16	100	f
289	46	96	2025-06-16	100	f
290	46	97	2025-06-16	100	f
291	46	98	2025-06-16	100	f
292	46	99	2025-06-16	100	f
293	46	100	2025-06-16	100	f
294	46	101	2025-06-16	100	f
295	46	102	2025-06-16	100	f
296	46	103	2025-06-16	100	f
297	46	104	2025-06-16	100	f
298	46	105	2025-06-16	100	f
299	46	106	2025-06-16	100	f
300	46	107	2025-06-16	96	f
301	46	116	2025-06-16	100	f
302	46	117	2025-06-16	100	f
303	46	118	2025-06-16	100	f
304	46	119	2025-06-16	100	f
305	46	120	2025-06-16	100	f
306	46	121	2025-06-16	100	f
307	46	122	2025-06-16	100	f
308	46	123	2025-06-16	100	f
309	46	124	2025-06-16	100	f
310	46	125	2025-06-16	100	f
311	46	126	2025-06-16	100	f
312	46	127	2025-06-16	100	f
313	46	141	2025-06-16	100	f
314	46	142	2025-06-16	100	f
315	46	143	2025-06-16	100	f
316	46	144	2025-06-16	100	f
317	46	145	2025-06-16	100	f
318	46	146	2025-06-16	100	f
319	46	128	2025-06-16	95	f
320	46	129	2025-06-16	100	f
321	46	130	2025-06-16	90	f
322	58	38	2025-06-16	100	f
323	57	55	2025-06-16	100	f
324	57	56	2025-06-16	100	f
325	56	26	2025-06-16	95	f
326	56	27	2025-06-16	100	f
327	56	28	2025-06-16	100	f
328	56	31	2025-06-16	100	f
329	55	26	2025-06-16	81	f
330	55	27	2025-06-16	81	f
331	55	28	2025-06-16	77	f
332	55	29	2025-06-16	80	f
333	55	30	2025-06-16	81	f
334	55	31	2025-06-16	80	f
335	55	32	2025-06-16	98	f
336	55	33	2025-06-16	100	f
337	55	34	2025-06-16	100	f
338	55	35	2025-06-16	100	f
339	55	38	2025-06-16	100	f
340	55	39	2025-06-16	100	f
341	55	40	2025-06-16	100	f
342	55	41	2025-06-16	100	f
343	55	42	2025-06-16	100	f
344	55	43	2025-06-16	90	f
345	55	44	2025-06-16	100	f
346	55	45	2025-06-16	100	f
347	55	46	2025-06-16	80	f
348	55	47	2025-06-16	100	f
349	55	48	2025-06-16	100	f
350	55	49	2025-06-16	100	f
351	55	50	2025-06-16	100	f
352	55	51	2025-06-16	100	f
353	55	52	2025-06-16	95	f
354	55	53	2025-06-16	95	f
355	55	54	2025-06-16	100	f
356	54	26	2025-06-16	100	f
357	54	27	2025-06-16	100	f
358	54	28	2025-06-16	96	f
359	53	26	2025-06-16	100	f
360	53	27	2025-06-16	100	f
361	53	28	2025-06-16	100	f
362	53	29	2025-06-16	100	f
363	53	30	2025-06-16	91	f
364	53	31	2025-06-16	100	f
365	53	32	2025-06-16	97	f
366	53	33	2025-06-16	100	f
367	52	38	2025-06-16	100	f
368	52	39	2025-06-16	100	f
369	52	40	2025-06-16	90	f
370	51	26	2025-06-16	91	f
371	51	27	2025-06-16	100	f
372	51	28	2025-06-16	100	f
373	50	59	2025-06-16	100	f
374	49	56	2025-06-16	98	f
375	49	57	2025-06-16	98	f
376	49	49	2025-06-16	100	f
377	49	50	2025-06-16	100	f
378	49	51	2025-06-16	100	f
379	49	52	2025-06-16	100	f
380	49	53	2025-06-16	99	f
381	49	54	2025-06-16	100	f
382	49	58	2025-06-16	90	f
383	49	59	2025-06-16	95	f
384	48	29	2025-06-16	83	f
385	48	30	2025-06-16	83	f
386	48	31	2025-06-16	83	f
387	48	56	2025-06-16	93	f
388	48	49	2025-06-16	95	f
389	47	26	2025-06-16	91	f
390	47	27	2025-06-16	100	f
391	47	28	2025-06-16	83	f
392	47	29	2025-06-16	83	f
393	47	30	2025-06-16	74	f
394	47	31	2025-06-16	88	f
395	47	32	2025-06-16	100	f
396	47	33	2025-06-16	100	f
397	47	34	2025-06-16	100	f
398	47	36	2025-06-16	100	f
399	47	38	2025-06-16	100	f
400	47	39	2025-06-16	90	f
401	47	40	2025-06-16	90	f
402	47	41	2025-06-16	100	f
403	47	42	2025-06-16	100	f
404	59	26	2025-06-16	100	f
405	59	27	2025-06-16	100	f
406	59	28	2025-06-16	100	f
407	59	29	2025-06-16	91	f
408	59	30	2025-06-16	100	f
409	59	31	2025-06-16	88	f
410	59	32	2025-06-16	100	f
411	59	33	2025-06-16	100	f
412	59	34	2025-06-16	100	f
413	59	128	2025-06-16	100	f
414	59	129	2025-06-16	100	f
415	59	130	2025-06-16	95	f
416	59	134	2025-06-16	100	f
417	59	135	2025-06-16	85	f
418	18	55	2025-06-17	100	f
419	18	56	2025-06-17	100	f
420	19	55	2025-06-17	100	f
421	20	55	2025-06-17	100	f
422	20	56	2025-06-17	100	f
423	21	55	2025-06-17	100	f
424	22	55	2025-06-17	100	f
425	23	55	2025-06-17	100	f
426	23	56	2025-06-17	100	f
427	24	55	2025-06-17	70	f
428	2	49	2025-06-17	88	f
429	2	147	2025-06-17	100	f
430	2	148	2025-06-17	96	f
431	2	149	2025-06-17	93	f
432	2	131	2025-06-17	100	f
433	2	83	2025-06-17	100	f
434	2	85	2025-06-17	90	f
435	2	87	2025-06-17	100	f
436	1	95	2025-06-19	100	f
437	3	97	2025-06-19	90	f
438	3	99	2025-06-19	100	f
439	25	55	2025-06-19	100	f
440	26	55	2025-06-19	100	f
441	26	56	2025-06-19	100	f
442	26	57	2025-06-19	100	f
443	1	55	2025-06-26	50	f
444	27	55	2025-06-26	50	f
445	28	55	2025-06-26	80	f
446	28	58	2025-06-26	80	f
447	29	55	2025-06-26	80	f
448	30	55	2025-06-26	60	f
449	31	55	2025-06-26	80	f
450	31	56	2025-06-26	70	f
451	31	57	2025-06-26	80	f
452	32	59	2025-06-26	95	f
453	32	60	2025-06-26	94	f
454	32	61	2025-06-26	95	f
455	32	62	2025-06-26	85	f
456	32	65	2025-06-26	100	f
457	32	74	2025-06-26	100	f
458	60	55	2025-07-24	100	f
459	60	56	2025-07-24	100	f
460	60	57	2025-07-24	100	f
461	60	58	2025-07-24	100	f
462	60	59	2025-07-24	95	f
463	60	61	2025-07-24	95	f
464	60	60	2025-07-24	95	f
465	60	62	2025-07-24	95	f
466	60	63	2025-07-24	95	f
467	60	64	2025-07-24	100	f
468	60	65	2025-07-24	90	f
469	61	38	2025-07-24	100	f
470	61	39	2025-07-24	100	f
471	61	40	2025-07-24	100	f
472	61	41	2025-07-24	100	f
473	61	43	2025-07-24	100	f
474	61	47	2025-07-24	100	f
475	61	49	2025-07-24	100	f
476	61	50	2025-07-24	90	f
477	61	51	2025-07-24	95	f
478	61	52	2025-07-24	95	f
479	61	83	2025-07-24	100	f
480	61	95	2025-07-24	100	f
481	61	96	2025-07-24	100	f
482	61	97	2025-07-24	100	f
483	62	38	2025-07-24	90	f
484	62	39	2025-07-24	100	f
485	62	40	2025-07-24	100	f
486	62	41	2025-07-24	100	f
487	62	42	2025-07-24	100	f
488	62	43	2025-07-24	100	f
489	62	44	2025-07-24	100	f
490	62	45	2025-07-24	100	f
491	62	46	2025-07-24	100	f
492	62	47	2025-07-24	100	f
493	62	48	2025-07-24	100	f
494	62	55	2025-07-24	100	f
495	62	56	2025-07-24	95	f
496	62	57	2025-07-24	89	f
497	62	49	2025-07-24	100	f
498	62	50	2025-07-24	100	f
499	62	51	2025-07-24	100	f
500	62	52	2025-07-24	100	f
501	62	53	2025-07-24	100	f
502	62	54	2025-07-24	100	f
503	62	58	2025-07-24	100	f
504	62	59	2025-07-24	100	f
505	62	61	2025-07-24	96	f
506	62	64	2025-07-24	75	f
507	62	65	2025-07-24	85	f
508	62	73	2025-07-24	100	f
509	62	79	2025-07-24	95	f
510	62	80	2025-07-24	100	f
511	62	81	2025-07-24	100	f
512	63	58	2025-07-24	95	f
513	63	59	2025-07-24	100	f
514	63	60	2025-07-24	100	f
515	63	61	2025-07-24	100	f
516	63	62	2025-07-24	100	f
517	63	63	2025-07-24	100	f
518	63	96	2025-07-24	98	f
519	63	97	2025-07-24	90	f
520	63	98	2025-07-24	85	f
521	64	26	2025-07-24	100	f
522	64	27	2025-07-24	100	f
523	64	28	2025-07-24	100	f
524	64	33	2025-07-24	100	f
525	64	95	2025-07-24	100	f
526	64	96	2025-07-24	91	f
527	64	98	2025-07-24	90	f
528	65	58	2025-07-24	100	f
529	65	59	2025-07-24	100	f
530	65	62	2025-07-24	95	f
531	65	63	2025-07-24	95	f
532	67	26	2025-07-24	100	f
533	67	27	2025-07-24	100	f
534	67	28	2025-07-24	83	f
535	67	29	2025-07-24	100	f
536	67	30	2025-07-24	90	f
537	67	31	2025-07-24	100	f
538	67	32	2025-07-24	100	f
539	67	33	2025-07-24	100	f
540	67	34	2025-07-24	100	f
541	67	35	2025-07-24	100	f
542	67	36	2025-07-24	100	f
543	67	37	2025-07-24	100	f
544	67	38	2025-07-24	100	f
545	67	39	2025-07-24	100	f
546	67	40	2025-07-24	100	f
547	67	41	2025-07-24	100	f
548	67	42	2025-07-24	100	f
549	67	43	2025-07-24	100	f
550	67	44	2025-07-24	100	f
551	67	45	2025-07-24	100	f
552	67	46	2025-07-24	90	f
553	67	47	2025-07-24	100	f
554	67	48	2025-07-24	100	f
555	68	38	2025-07-24	100	f
556	68	39	2025-07-24	100	f
557	68	40	2025-07-24	100	f
558	68	41	2025-07-24	100	f
559	68	42	2025-07-24	90	f
560	68	43	2025-07-24	100	f
561	68	44	2025-07-24	100	f
562	68	45	2025-07-24	100	f
563	68	47	2025-07-24	100	f
564	68	48	2025-07-24	100	f
565	68	55	2025-07-24	100	f
566	68	56	2025-07-24	96	f
567	68	57	2025-07-24	100	f
568	68	49	2025-07-24	100	f
569	68	50	2025-07-24	100	f
570	68	51	2025-07-24	90	f
571	68	52	2025-07-24	100	f
572	68	53	2025-07-24	100	f
573	68	54	2025-07-24	100	f
574	69	38	2025-07-24	100	f
575	69	39	2025-07-24	100	f
576	69	40	2025-07-24	100	f
577	69	41	2025-07-24	100	f
578	69	42	2025-07-24	100	f
579	69	43	2025-07-24	100	f
580	69	44	2025-07-24	100	f
581	69	45	2025-07-24	100	f
582	69	46	2025-07-24	100	f
583	69	47	2025-07-24	90	f
584	69	48	2025-07-24	100	f
585	69	51	2025-07-24	100	f
586	69	52	2025-07-24	100	f
587	69	53	2025-07-24	90	f
588	69	54	2025-07-24	100	f
589	69	66	2025-07-24	95	f
590	69	67	2025-07-24	90	f
591	69	68	2025-07-24	90	f
592	69	70	2025-07-24	100	f
593	69	71	2025-07-24	100	f
594	70	38	2025-07-24	100	f
595	70	39	2025-07-24	100	f
596	70	40	2025-07-24	100	f
597	70	41	2025-07-24	100	f
598	70	42	2025-07-24	100	f
599	70	43	2025-07-24	100	f
600	70	44	2025-07-24	100	f
601	70	45	2025-07-24	100	f
602	70	47	2025-07-24	90	f
603	70	48	2025-07-24	100	f
604	70	55	2025-07-24	100	f
605	70	56	2025-07-24	98	f
606	70	49	2025-07-24	100	f
607	70	50	2025-07-24	100	f
608	70	51	2025-07-24	95	f
609	70	52	2025-07-24	95	f
610	70	53	2025-07-24	85	f
611	70	54	2025-07-24	90	f
612	1	38	2025-07-24	100	f
613	1	40	2025-07-24	90	f
614	1	43	2025-07-24	100	f
615	1	44	2025-07-24	100	f
616	1	46	2025-07-24	90	f
617	1	47	2025-07-24	100	f
618	1	48	2025-07-24	100	f
619	1	56	2025-07-24	100	f
620	1	57	2025-07-24	100	f
621	71	49	2025-07-24	100	f
622	71	50	2025-07-24	100	f
623	71	51	2025-07-24	100	f
624	71	52	2025-07-24	100	f
625	71	53	2025-07-24	100	f
626	71	54	2025-07-24	100	f
627	71	66	2025-07-24	100	f
628	71	67	2025-07-24	100	f
629	71	68	2025-07-24	100	f
630	71	69	2025-07-24	100	f
631	71	70	2025-07-24	100	f
632	71	71	2025-07-24	100	f
633	71	72	2025-07-24	100	f
634	71	79	2025-07-24	100	f
635	71	80	2025-07-24	100	f
636	72	49	2025-07-24	100	f
637	72	50	2025-07-24	100	f
638	72	51	2025-07-24	100	f
639	72	52	2025-07-24	100	f
640	72	54	2025-07-24	95	f
641	72	66	2025-07-24	100	f
642	72	67	2025-07-24	100	f
643	72	68	2025-07-24	95	f
644	72	69	2025-07-24	100	f
645	72	70	2025-07-24	100	f
646	72	71	2025-07-24	100	f
647	72	72	2025-07-24	100	f
648	72	79	2025-07-24	100	f
649	72	80	2025-07-24	96	f
650	72	81	2025-07-24	100	f
651	72	82	2025-07-24	100	f
652	72	53	2025-07-24	100	f
653	71	111	2025-07-28	100	f
654	71	108	2025-07-28	100	f
655	71	109	2025-07-28	100	f
656	71	110	2025-07-28	100	f
657	71	112	2025-07-28	100	f
658	71	113	2025-07-28	100	f
659	71	115	2025-07-28	100	f
660	71	83	2025-07-28	100	f
661	71	84	2025-07-28	100	f
662	72	83	2025-07-28	95	f
663	72	84	2025-07-28	95	f
664	11	34	2025-07-29	100	f
665	48	54	2025-07-29	100	f
666	3	101	2025-07-29	90	f
667	18	66	2025-07-29	100	f
668	69	49	2025-07-30	90	f
669	69	50	2025-07-30	100	f
670	67	55	2025-07-30	100	f
671	67	56	2025-07-30	100	f
672	67	57	2025-07-30	100	f
673	71	55	2025-07-30	100	f
674	71	56	2025-07-30	100	f
675	71	57	2025-07-30	100	f
676	71	114	2025-07-30	97	f
677	72	55	2025-07-30	100	f
678	72	56	2025-07-30	100	f
679	72	57	2025-07-30	100	f
680	72	108	2025-07-30	100	f
681	72	109	2025-07-30	100	f
682	72	110	2025-07-30	100	f
683	72	111	2025-07-30	100	f
684	72	112	2025-07-30	100	f
685	72	113	2025-07-30	93	f
686	72	114	2025-07-30	91	f
687	72	115	2025-07-30	93	f
688	1	49	2025-07-30	100	f
689	1	50	2025-07-30	100	f
690	1	51	2025-07-30	100	f
691	1	52	2025-07-30	95	f
692	1	53	2025-07-30	95	f
693	1	54	2025-07-30	95	f
694	70	73	2025-07-31	85	f
695	62	83	2025-07-31	100	f
696	62	82	2025-07-31	100	f
697	72	73	2025-07-31	100	f
698	72	97	2025-07-31	98	f
699	72	98	2025-07-31	100	f
700	72	99	2025-07-31	99	f
701	72	100	2025-07-31	100	f
702	72	101	2025-07-31	100	f
703	72	102	2025-07-31	100	f
704	72	103	2025-07-31	100	f
705	72	104	2025-07-31	100	f
706	72	105	2025-07-31	100	f
707	72	106	2025-07-31	100	f
708	72	107	2025-07-31	100	f
709	71	73	2025-07-31	100	f
710	71	81	2025-07-31	100	f
711	71	82	2025-07-31	100	f
712	71	97	2025-07-31	100	f
713	71	98	2025-07-31	100	f
714	71	99	2025-07-31	100	f
715	71	100	2025-07-31	100	f
716	71	101	2025-07-31	100	f
717	71	102	2025-07-31	100	f
718	71	103	2025-07-31	100	f
719	71	104	2025-07-31	100	f
720	71	105	2025-07-31	100	f
721	71	106	2025-07-31	100	f
722	71	107	2025-07-31	100	f
723	9	46	2025-07-31	100	f
724	12	32	2025-07-31	100	f
725	11	32	2025-07-31	100	f
726	9	36	2025-07-31	100	f
727	11	35	2025-07-31	100	f
728	1	154	2025-08-05	98	f
729	1	155	2025-08-05	98	f
730	1	157	2025-08-05	98	f
731	1	156	2025-08-05	100	f
732	62	155	2025-08-05	100	f
733	62	159	2025-08-05	100	f
734	62	154	2025-08-05	98	f
735	62	157	2025-08-05	100	f
736	67	152	2025-08-05	100	f
737	67	153	2025-08-05	100	f
738	67	154	2025-08-05	100	f
739	67	150	2025-08-05	94	f
740	67	151	2025-08-05	97	f
741	67	155	2025-08-05	98	f
742	68	154	2025-08-05	98	f
743	68	156	2025-08-05	99	f
744	68	157	2025-08-05	98	f
745	69	154	2025-08-05	100	f
746	69	155	2025-08-05	98	f
747	70	154	2025-08-05	100	f
748	70	155	2025-08-05	98	f
749	70	157	2025-08-05	94	f
750	70	159	2025-08-05	85	f
751	70	46	2025-08-05	100	f
752	70	57	2025-08-05	100	f
753	69	157	2025-08-05	97	f
754	18	57	2025-08-12	88	f
755	2	61	2025-08-12	95	f
756	2	139	2025-08-12	70	f
757	2	132	2025-08-12	72	f
758	3	95	2025-08-12	100	f
759	48	49	2025-08-21	100	f
760	48	50	2025-08-21	100	f
761	48	51	2025-08-21	100	f
762	48	52	2025-08-21	100	f
763	48	53	2025-08-21	95	f
764	4	49	2025-08-21	100	f
765	4	50	2025-08-21	100	f
766	4	51	2025-08-21	100	f
767	4	54	2025-08-21	90	f
768	4	38	2025-08-21	100	f
769	4	39	2025-08-21	100	f
770	4	40	2025-08-21	100	f
771	4	41	2025-08-21	100	f
772	4	42	2025-08-21	100	f
773	74	49	2025-08-21	95	f
774	74	51	2025-08-21	100	f
775	74	50	2025-08-21	90	f
776	73	38	2025-08-21	100	f
777	4	45	2025-08-21	100	f
778	4	44	2025-08-21	100	f
779	33	56	2025-08-22	100	f
780	33	57	2025-08-22	100	f
781	33	59	2025-08-22	100	f
782	33	60	2025-08-22	100	f
783	33	61	2025-08-22	95	f
784	33	62	2025-08-22	100	f
785	33	63	2025-08-22	95	f
786	34	55	2025-08-22	95	f
787	34	57	2025-08-22	95	f
788	35	56	2025-08-22	98	f
789	35	57	2025-08-22	98	f
790	35	59	2025-08-22	95	f
791	35	62	2025-08-22	90	f
792	36	55	2025-08-22	87	f
793	36	56	2025-08-22	93	f
794	36	57	2025-08-22	85	f
795	36	58	2025-08-22	90	f
796	36	59	2025-08-22	100	f
797	36	60	2025-08-22	95	f
798	36	61	2025-08-22	95	f
799	36	62	2025-08-22	100	f
800	36	63	2025-08-22	100	f
801	36	64	2025-08-22	95	f
802	36	65	2025-08-22	100	f
803	36	95	2025-08-22	100	f
804	36	96	2025-08-22	100	f
805	36	97	2025-08-22	95	f
806	36	98	2025-08-22	95	f
807	36	99	2025-08-22	100	f
808	36	100	2025-08-22	100	f
809	36	101	2025-08-22	100	f
810	36	102	2025-08-22	100	f
811	36	103	2025-08-22	100	f
812	36	104	2025-08-22	100	f
813	36	105	2025-08-22	100	f
814	36	106	2025-08-22	100	f
815	36	107	2025-08-22	100	f
816	37	62	2025-08-22	100	f
817	38	58	2025-08-22	90	f
818	38	59	2025-08-22	100	f
819	38	60	2025-08-22	95	f
820	39	58	2025-08-22	90	f
821	39	59	2025-08-22	95	f
822	39	60	2025-08-22	95	f
823	40	57	2025-08-22	83	f
824	41	60	2025-08-22	80	f
825	75	55	2025-08-22	100	f
826	75	56	2025-08-22	84	f
827	75	57	2025-08-22	81	f
828	75	60	2025-08-22	100	f
829	75	61	2025-08-22	95	f
830	75	62	2025-08-22	90	f
831	75	63	2025-08-22	95	f
832	75	64	2025-08-22	90	f
833	76	55	2025-08-22	100	f
834	76	56	2025-08-22	100	f
835	76	57	2025-08-22	100	f
836	76	58	2025-08-22	100	f
837	76	59	2025-08-22	100	f
838	76	60	2025-08-22	100	f
839	76	73	2025-08-22	100	f
840	78	55	2025-08-22	100	f
841	79	55	2025-08-22	80	f
842	80	55	2025-08-22	100	f
843	80	56	2025-08-22	100	f
844	80	68	2025-08-22	80	f
845	80	67	2025-08-22	90	f
846	80	66	2025-08-22	80	f
847	81	55	2025-08-22	100	f
848	81	56	2025-08-22	100	f
849	82	55	2025-08-22	100	f
850	82	56	2025-08-22	96	f
851	83	55	2025-08-22	100	f
852	83	56	2025-08-22	100	f
853	83	57	2025-08-22	100	f
854	84	55	2025-08-22	90	f
855	84	56	2025-08-22	93	f
856	85	55	2025-08-26	96	f
857	85	56	2025-08-26	86	f
858	85	58	2025-08-26	90	f
859	86	55	2025-08-26	100	f
860	86	56	2025-08-26	96	f
861	86	59	2025-08-26	95	f
862	87	55	2025-08-26	85	f
863	87	56	2025-08-26	90	f
864	88	55	2025-08-26	100	f
865	88	56	2025-08-26	100	f
866	88	57	2025-08-26	100	f
867	88	49	2025-08-26	100	f
868	88	50	2025-08-26	100	f
869	88	51	2025-08-26	90	f
870	88	52	2025-08-26	95	f
871	88	53	2025-08-26	100	f
872	88	54	2025-08-26	95	f
873	88	157	2025-08-26	96	f
874	88	58	2025-08-26	80	f
875	88	59	2025-08-26	90	f
876	88	60	2025-08-26	70	f
877	88	61	2025-08-26	70	f
878	88	62	2025-08-26	95	f
879	88	63	2025-08-26	100	f
880	88	65	2025-08-26	100	f
881	88	73	2025-08-26	100	f
882	88	74	2025-08-26	100	f
883	88	75	2025-08-26	92	f
884	88	76	2025-08-26	96	f
885	88	77	2025-08-26	97	f
886	88	78	2025-08-26	100	f
887	88	95	2025-08-26	100	f
888	88	96	2025-08-26	100	f
889	88	98	2025-08-26	100	f
890	88	105	2025-08-26	100	f
891	88	116	2025-08-26	95	f
892	88	117	2025-08-26	100	f
893	88	136	2025-08-26	75	f
894	88	138	2025-08-26	78	f
895	89	56	2025-08-26	100	f
896	90	56	2025-08-26	98	f
897	91	56	2025-08-26	84	f
898	92	55	2025-08-26	100	f
899	92	56	2025-08-26	100	f
900	92	57	2025-08-26	100	f
901	92	156	2025-08-26	100	f
902	92	59	2025-08-26	100	f
903	92	60	2025-08-26	100	f
904	92	61	2025-08-26	100	f
905	92	62	2025-08-26	95	f
906	92	63	2025-08-26	95	f
907	92	64	2025-08-26	95	f
908	92	159	2025-08-26	100	f
909	92	74	2025-08-26	88	f
910	92	75	2025-08-26	85	f
911	92	97	2025-08-26	95	f
912	92	99	2025-08-26	90	f
913	92	100	2025-08-26	95	f
914	92	101	2025-08-26	100	f
915	92	102	2025-08-26	100	f
916	92	103	2025-08-26	100	f
917	92	104	2025-08-26	100	f
918	92	105	2025-08-26	100	f
919	92	106	2025-08-26	100	f
920	92	107	2025-08-26	95	f
921	92	116	2025-08-26	100	f
922	92	117	2025-08-26	100	f
923	92	118	2025-08-26	100	f
924	92	119	2025-08-26	100	f
925	92	120	2025-08-26	90	f
926	92	121	2025-08-26	100	f
927	92	122	2025-08-26	100	f
928	92	123	2025-08-26	100	f
929	92	124	2025-08-26	100	f
930	92	125	2025-08-26	100	f
931	92	127	2025-08-26	100	f
932	92	134	2025-08-26	75	f
933	92	135	2025-08-26	96	f
934	92	139	2025-08-26	87	f
935	93	55	2025-08-26	100	f
936	94	55	2025-08-26	100	f
937	94	56	2025-08-26	100	f
938	94	57	2025-08-26	100	f
939	94	156	2025-08-26	100	f
940	94	60	2025-08-26	100	f
941	94	61	2025-08-26	94	f
942	94	62	2025-08-26	85	f
943	94	63	2025-08-26	85	f
944	94	63	2025-08-26	100	f
945	94	64	2025-08-26	90	f
946	96	55	2025-08-26	100	f
947	97	56	2025-08-26	100	f
948	97	57	2025-08-26	100	f
949	97	58	2025-08-26	100	f
950	97	59	2025-08-26	100	f
951	97	60	2025-08-26	100	f
952	97	61	2025-08-26	100	f
953	97	62	2025-08-26	90	f
954	97	63	2025-08-26	100	f
955	97	64	2025-08-26	100	f
956	98	55	2025-08-26	100	f
957	99	55	2025-08-26	100	f
958	99	56	2025-08-26	94	f
959	99	57	2025-08-26	100	f
960	99	58	2025-08-26	100	f
961	99	59	2025-08-26	100	f
962	99	60	2025-08-26	92	f
963	27	55	2025-08-26	80	f
964	100	55	2025-08-26	100	f
965	100	56	2025-08-26	100	f
966	100	57	2025-08-26	100	f
967	100	156	2025-08-26	100	f
968	100	58	2025-08-26	90	f
969	100	59	2025-08-26	95	f
970	100	60	2025-08-26	95	f
971	100	61	2025-08-26	95	f
972	100	62	2025-08-26	95	f
973	100	63	2025-08-26	95	f
974	100	64	2025-08-26	95	f
975	100	65	2025-08-26	95	f
976	100	73	2025-08-26	100	f
977	100	74	2025-08-26	100	f
978	100	75	2025-08-26	100	f
979	100	76	2025-08-26	97	f
980	100	78	2025-08-26	100	f
981	100	160	2025-08-26	98	f
982	100	95	2025-08-26	90	f
983	100	96	2025-08-26	100	f
984	100	97	2025-08-26	100	f
985	100	98	2025-08-26	90	f
986	100	99	2025-08-26	100	f
987	100	101	2025-08-26	100	f
988	100	103	2025-08-26	100	f
989	100	104	2025-08-26	98	f
990	100	105	2025-08-26	100	f
991	100	106	2025-08-26	100	f
992	100	107	2025-08-26	100	f
993	100	116	2025-08-26	99	f
994	100	117	2025-08-26	98	f
995	100	118	2025-08-26	100	f
996	100	119	2025-08-26	100	f
997	100	120	2025-08-26	100	f
998	100	121	2025-08-26	100	f
999	100	122	2025-08-26	100	f
1000	100	123	2025-08-26	100	f
1001	100	124	2025-08-26	100	f
1002	100	125	2025-08-26	100	f
1003	100	126	2025-08-26	100	f
1004	100	128	2025-08-26	90	f
1005	100	129	2025-08-26	95	f
1006	100	130	2025-08-26	90	f
1007	100	134	2025-08-26	84	f
1008	100	139	2025-08-26	88	f
1009	101	55	2025-08-26	100	f
1010	101	57	2025-08-26	83	f
1011	101	58	2025-08-26	90	f
1012	102	55	2025-08-26	86	f
1013	102	56	2025-08-26	85	f
1014	103	55	2025-08-26	80	f
1015	103	56	2025-08-26	85	f
1016	103	57	2025-08-26	86	f
1017	104	55	2025-08-26	100	f
1018	104	56	2025-08-26	75	f
1019	104	57	2025-08-26	91	f
1020	104	156	2025-08-26	89	f
1021	105	55	2025-08-26	100	f
1022	105	56	2025-08-26	100	f
1023	105	57	2025-08-26	100	f
1024	105	58	2025-08-26	100	f
1025	105	59	2025-08-26	100	f
1026	105	61	2025-08-26	90	f
1027	105	62	2025-08-26	100	f
1028	105	63	2025-08-26	100	f
1029	105	64	2025-08-26	100	f
1030	105	65	2025-08-26	100	f
1031	105	73	2025-08-26	100	f
1032	105	74	2025-08-26	100	f
1033	105	75	2025-08-26	95	f
1034	105	76	2025-08-26	100	f
1035	105	77	2025-08-26	100	f
1036	105	95	2025-08-26	90	f
1037	105	96	2025-08-26	100	f
1038	105	97	2025-08-26	95	f
1039	106	55	2025-08-26	100	f
1040	107	55	2025-08-26	90	f
1041	108	55	2025-08-26	100	f
1042	109	55	2025-08-26	100	f
1043	109	56	2025-08-26	100	f
1044	109	57	2025-08-26	100	f
1045	110	55	2025-08-26	100	f
1046	110	57	2025-08-26	100	f
1047	110	67	2025-08-26	90	f
1048	110	68	2025-08-26	90	f
1049	111	26	2025-08-26	100	f
1050	111	27	2025-08-26	100	f
1051	111	28	2025-08-26	89	f
1052	111	150	2025-08-26	96	f
1053	111	29	2025-08-26	100	f
1054	111	30	2025-08-26	100	f
1055	111	31	2025-08-26	100	f
1056	111	151	2025-08-26	100	f
1057	111	55	2025-08-26	90	f
1058	111	56	2025-08-26	90	f
1059	112	26	2025-08-26	100	f
1060	112	27	2025-08-26	100	f
1061	112	28	2025-08-26	100	f
1062	112	29	2025-08-26	100	f
1063	112	30	2025-08-26	100	f
1064	112	31	2025-08-26	100	f
1065	113	26	2025-08-26	100	f
1066	113	27	2025-08-26	100	f
1067	113	28	2025-08-26	93	f
1068	113	29	2025-08-26	90	f
1069	113	30	2025-08-26	100	f
1070	113	31	2025-08-26	100	f
1071	42	150	2025-08-26	80	f
1072	42	151	2025-08-26	80	f
1073	42	152	2025-08-26	100	f
1074	42	153	2025-08-26	100	f
1075	43	150	2025-08-26	80	f
1076	43	151	2025-08-26	83	f
1077	43	152	2025-08-26	100	f
1078	43	153	2025-08-26	100	f
1079	45	157	2025-08-26	98	f
1080	44	150	2025-08-26	75	f
1081	44	151	2025-08-26	83	f
1082	44	152	2025-08-26	95	f
1083	46	150	2025-08-26	100	f
1084	46	151	2025-08-26	100	f
1085	46	152	2025-08-26	100	f
1086	46	153	2025-08-26	100	f
1087	46	157	2025-08-26	100	f
1088	46	158	2025-08-26	98	f
1089	46	159	2025-08-26	100	f
1090	46	161	2025-08-26	99	f
1091	46	164	2025-08-26	95	f
1092	47	150	2025-08-26	92	f
1093	47	151	2025-08-26	82	f
1094	47	152	2025-08-26	100	f
1095	47	154	2025-08-26	96	f
1096	48	151	2025-08-26	83	f
1097	49	156	2025-08-26	99	f
1098	49	157	2025-08-26	99	f
1099	59	150	2025-08-26	100	f
1100	59	151	2025-08-26	93	f
1101	59	152	2025-08-26	100	f
1102	51	150	2025-08-26	97	f
1103	53	150	2025-08-26	100	f
1104	53	151	2025-08-26	97	f
1105	55	150	2025-08-26	80	f
1106	55	151	2025-08-26	80	f
1107	55	154	2025-08-26	100	f
1108	114	49	2025-08-26	90	f
1109	115	49	2025-08-26	70	f
1110	116	49	2025-08-26	70	f
1111	117	49	2025-08-26	80	f
1112	118	49	2025-08-26	80	f
1113	119	83	2025-08-26	99	f
1114	120	83	2025-08-26	99	f
1115	121	83	2025-08-26	95	f
1116	122	83	2025-08-26	95	f
1117	123	83	2025-08-26	92	f
1118	124	83	2025-08-26	90	f
1119	125	83	2025-08-26	80	f
1120	126	83	2025-08-26	75	f
1121	127	83	2025-08-26	99	f
1122	128	79	2025-08-26	99	f
1123	128	80	2025-08-26	97	f
1124	129	79	2025-08-26	90	f
1125	129	80	2025-08-26	93	f
1126	130	79	2025-08-26	85	f
1127	130	80	2025-08-26	70	f
1128	131	80	2025-08-26	80	f
1129	132	80	2025-08-26	95	f
1130	133	80	2025-08-26	95	f
1131	134	26	2025-08-26	91	f
1132	134	27	2025-08-26	100	f
1133	134	28	2025-08-26	83	f
1134	134	150	2025-08-26	91	f
1135	134	29	2025-08-26	100	f
1136	134	30	2025-08-26	100	f
1137	134	31	2025-08-26	94	f
1138	134	151	2025-08-26	98	f
1139	134	32	2025-08-26	100	f
1140	134	33	2025-08-26	100	f
1141	135	26	2025-08-26	100	f
1142	135	27	2025-08-26	100	f
1143	135	28	2025-08-26	100	f
1144	135	150	2025-08-26	100	f
1145	135	29	2025-08-26	100	f
1146	135	30	2025-08-26	100	f
1147	135	31	2025-08-26	100	f
1148	135	151	2025-08-26	100	f
1149	135	32	2025-08-26	100	f
1150	135	33	2025-08-26	100	f
1151	135	34	2025-08-26	100	f
1152	135	35	2025-08-26	100	f
1153	135	36	2025-08-26	100	f
1154	135	37	2025-08-26	99	f
1155	4	27	2025-08-26	100	f
1156	4	150	2025-08-26	97	f
1157	157	55	2025-08-28	100	f
1158	158	55	2025-08-28	100	f
1159	159	55	2025-08-28	100	f
1160	160	55	2025-08-28	100	f
1161	161	49	2025-08-28	100	f
1162	161	50	2025-08-28	100	f
1163	161	51	2025-08-28	95	f
1164	162	26	2025-08-28	83	f
1165	162	27	2025-08-28	100	f
1166	162	28	2025-08-28	100	f
1167	162	150	2025-08-28	94	f
1168	162	49	2025-08-28	95	f
1169	162	50	2025-08-28	100	f
1170	163	50	2025-08-28	100	f
1171	163	27	2025-08-28	100	f
1172	163	28	2025-08-28	100	f
1173	163	49	2025-08-28	95	f
1174	164	49	2025-08-28	85	f
1175	164	50	2025-08-28	100	f
1176	165	49	2025-08-28	70	f
1177	165	50	2025-08-28	55	f
1178	166	49	2025-08-28	90	f
1179	166	50	2025-08-28	100	f
1180	167	51	2025-08-28	95	f
1181	168	49	2025-08-28	90	f
1182	168	50	2025-08-28	90	f
1183	168	51	2025-08-28	95	f
1184	169	50	2025-09-04	85	f
1185	170	26	2025-09-04	100	f
1186	170	27	2025-09-04	100	f
1187	170	28	2025-09-04	94	f
1188	170	150	2025-09-04	98	f
1189	171	26	2025-09-04	83	f
1190	171	27	2025-09-04	83	f
1191	171	28	2025-09-04	77	f
1192	171	150	2025-09-04	83	f
1193	172	26	2025-09-04	91	f
1194	172	27	2025-09-04	100	f
1195	172	28	2025-09-04	94	f
1196	172	150	2025-09-04	95	f
1197	173	26	2025-09-04	91	f
1198	173	27	2025-09-04	74	f
1199	173	28	2025-09-04	83	f
1200	173	150	2025-09-04	82	f
1201	174	26	2025-09-04	74	f
1202	174	27	2025-09-04	74	f
1203	175	26	2025-09-04	100	f
1204	175	27	2025-09-04	91	f
1205	176	26	2025-09-04	83	f
1206	176	27	2025-09-04	66	f
1207	177	26	2025-09-04	91	f
1208	177	27	2025-09-04	74	f
1209	177	28	2025-09-04	83	f
1210	177	150	2025-09-04	82	f
1211	178	51	2025-09-04	85	f
1212	179	50	2025-09-04	55	f
1213	180	27	2025-09-04	91	f
1214	181	27	2025-09-04	83	f
1215	182	72	2025-09-04	100	f
1216	183	102	2025-09-04	100	f
1217	184	97	2025-09-04	100	f
1218	185	142	2025-09-04	90	f
1219	185	143	2025-09-04	100	f
1220	185	144	2025-09-04	100	f
1221	185	145	2025-09-04	95	f
1222	185	146	2025-09-04	100	f
1223	185	134	2025-09-04	100	f
1224	185	135	2025-09-04	100	f
1225	185	136	2025-09-04	100	f
1226	185	137	2025-09-04	96	f
1227	185	138	2025-09-04	95	f
1228	185	139	2025-09-04	100	f
1229	185	131	2025-09-04	100	f
1230	185	132	2025-09-04	100	f
1231	186	27	2025-09-04	100	f
1232	186	55	2025-09-04	98	f
1233	186	56	2025-09-04	100	f
1234	186	57	2025-09-04	100	f
1235	186	156	2025-09-04	99	f
1236	186	66	2025-09-04	80	f
1237	186	67	2025-09-04	95	f
1238	186	68	2025-09-04	92	f
1239	186	69	2025-09-04	85	f
1240	186	72	2025-09-04	95	f
1241	186	71	2025-09-04	80	f
1242	187	55	2025-09-04	100	f
1243	187	56	2025-09-04	100	f
1244	187	57	2025-09-04	100	f
1245	187	156	2025-09-04	100	f
1246	187	67	2025-09-04	88	f
1247	187	68	2025-09-04	92	f
1248	187	69	2025-09-04	85	f
1249	187	70	2025-09-04	100	f
1250	187	71	2025-09-04	90	f
1251	187	72	2025-09-04	100	f
1252	188	26	2025-09-04	90	f
1253	189	55	2025-09-04	80	f
1254	189	56	2025-09-04	92	f
1255	189	57	2025-09-04	100	f
1256	189	156	2025-09-04	90	f
1257	189	66	2025-09-04	90	f
1258	189	141	2025-09-04	70	f
1259	189	142	2025-09-04	89	f
1260	189	143	2025-09-04	79	f
1261	190	55	2025-09-04	100	f
1262	190	56	2025-09-04	100	f
1263	190	57	2025-09-04	97	f
1264	190	156	2025-09-04	99	f
1265	190	68	2025-09-04	100	f
1266	190	71	2025-09-04	90	f
1267	191	55	2025-09-04	100	f
1268	191	56	2025-09-04	95	f
1269	191	57	2025-09-04	100	f
1270	191	156	2025-09-04	98	f
1271	191	66	2025-09-04	90	f
1272	191	67	2025-09-04	95	f
1273	192	55	2025-09-04	80	f
1274	192	56	2025-09-04	98	f
1275	192	57	2025-09-04	97	f
1276	192	156	2025-09-04	92	f
1277	193	26	2025-09-09	80	f
1278	193	27	2025-09-09	83	f
1279	193	28	2025-09-09	77	f
1280	193	150	2025-09-09	80	f
1281	193	29	2025-09-09	83	f
1282	193	30	2025-09-09	91	f
1283	193	31	2025-09-09	83	f
1284	193	151	2025-09-09	85	f
1285	194	95	2025-09-09	100	f
1286	194	96	2025-09-09	100	f
1287	194	97	2025-09-09	100	f
1288	194	98	2025-09-09	100	f
1289	194	99	2025-09-09	100	f
1290	195	38	2025-09-09	100	f
1291	195	39	2025-09-09	100	f
1292	195	40	2025-09-09	90	f
1293	195	41	2025-09-09	100	f
1294	195	42	2025-09-09	80	f
1295	195	154	2025-09-09	94	f
1296	195	43	2025-09-09	100	f
1297	195	49	2025-09-09	100	f
1298	196	26	2025-09-09	100	f
1299	196	27	2025-09-09	100	f
1300	196	28	2025-09-09	100	f
1301	196	150	2025-09-09	100	f
1302	196	29	2025-09-09	100	f
1303	196	30	2025-09-09	90	f
1304	196	32	2025-09-09	90	f
1305	196	33	2025-09-09	100	f
1306	196	34	2025-09-09	100	f
1307	196	56	2025-09-09	66	f
1308	7	150	2025-09-11	84	f
1309	197	55	2025-09-11	100	f
1310	197	56	2025-09-11	95	f
1311	197	57	2025-09-11	95	f
1312	197	58	2025-09-11	100	f
1313	197	59	2025-09-11	95	f
1314	197	60	2025-09-11	100	f
1315	197	61	2025-09-11	98	f
1316	197	62	2025-09-11	100	f
1317	197	63	2025-09-11	95	f
1318	197	64	2025-09-11	95	f
1319	197	65	2025-09-11	100	f
1320	197	74	2025-09-11	100	f
1321	197	75	2025-09-11	98	f
1322	197	76	2025-09-11	100	f
1323	197	77	2025-09-11	95	f
1324	197	78	2025-09-11	100	f
1325	197	95	2025-09-11	98	f
1326	197	96	2025-09-11	100	f
1327	197	97	2025-09-11	100	f
1328	197	98	2025-09-11	98	f
1329	197	99	2025-09-11	100	f
1330	197	100	2025-09-11	100	f
1331	197	101	2025-09-11	100	f
1332	197	102	2025-09-11	100	f
1333	197	103	2025-09-11	100	f
1334	197	104	2025-09-11	100	f
1335	197	105	2025-09-11	100	f
1336	197	106	2025-09-11	100	f
1337	197	107	2025-09-11	100	f
1338	197	116	2025-09-11	100	f
1339	197	117	2025-09-11	96	f
1340	197	118	2025-09-11	100	f
1341	197	119	2025-09-11	100	f
1342	198	26	2025-09-11	100	f
1343	198	27	2025-09-11	100	f
1344	198	28	2025-09-11	87	f
1345	198	29	2025-09-11	100	f
1346	198	30	2025-09-11	100	f
1347	198	31	2025-09-11	100	f
1348	198	32	2025-09-11	100	f
1349	198	36	2025-09-11	100	f
1350	198	37	2025-09-11	100	f
1351	198	35	2025-09-11	100	f
1352	198	33	2025-09-11	100	f
1353	198	34	2025-09-11	100	f
1354	198	38	2025-09-11	90	f
1355	198	39	2025-09-11	90	f
1356	198	40	2025-09-11	100	f
1357	198	43	2025-09-11	100	f
1358	198	44	2025-09-11	100	f
1359	198	45	2025-09-11	100	f
1360	198	46	2025-09-11	100	f
1361	198	47	2025-09-11	100	f
1362	198	48	2025-09-11	100	f
1363	198	155	2025-09-11	100	f
1364	198	55	2025-09-11	100	f
1365	198	56	2025-09-11	100	f
1366	198	57	2025-09-11	100	f
1367	198	49	2025-09-11	100	f
1368	198	50	2025-09-11	95	f
1369	198	51	2025-09-11	85	f
1370	198	58	2025-09-11	100	f
1371	198	59	2025-09-11	95	f
1372	198	60	2025-09-11	95	f
1373	198	61	2025-09-11	100	f
1374	198	62	2025-09-11	95	f
1375	198	63	2025-09-11	95	f
1376	198	64	2025-09-11	100	f
1377	198	65	2025-09-11	90	f
1378	199	26	2025-09-11	90	f
1379	199	27	2025-09-11	91	f
1380	199	28	2025-09-11	83	f
1381	199	29	2025-09-11	83	f
1382	199	30	2025-09-11	91	f
1383	199	31	2025-09-11	94	f
1384	199	32	2025-09-11	100	f
1385	199	33	2025-09-11	100	f
1386	199	34	2025-09-11	96	f
1387	199	36	2025-09-11	100	f
1388	199	37	2025-09-11	96	f
1389	199	38	2025-09-11	100	f
1390	199	39	2025-09-11	70	f
1391	199	40	2025-09-11	90	f
1392	199	41	2025-09-11	100	f
1393	199	42	2025-09-11	95	f
1394	200	26	2025-09-11	100	f
1395	200	27	2025-09-11	100	f
1396	200	28	2025-09-11	100	f
1397	200	150	2025-09-11	100	f
1398	200	29	2025-09-11	91	f
1399	200	30	2025-09-11	100	f
1400	200	83	2025-09-11	100	f
1401	200	84	2025-09-11	100	f
1402	200	85	2025-09-11	100	f
1403	201	59	2025-09-11	70	f
1404	201	60	2025-09-11	100	f
1405	202	59	2025-09-11	100	f
1406	203	58	2025-09-11	100	f
1407	203	59	2025-09-11	80	f
1408	203	60	2025-09-11	95	f
1409	204	59	2025-09-11	100	f
1410	205	58	2025-09-11	100	f
1411	205	59	2025-09-11	100	f
1412	205	60	2025-09-11	85	f
1413	206	58	2025-09-11	80	f
1414	206	59	2025-09-11	90	f
1415	207	58	2025-09-11	100	f
1416	207	59	2025-09-11	100	f
1417	207	60	2025-09-11	85	f
1418	208	58	2025-09-11	90	f
1419	209	58	2025-09-11	80	f
1420	209	59	2025-09-11	90	f
1421	209	60	2025-09-11	92	f
1422	209	61	2025-09-11	100	f
1423	209	62	2025-09-11	90	f
1424	209	63	2025-09-11	95	f
1425	209	64	2025-09-11	80	f
1426	209	65	2025-09-11	90	f
1427	210	58	2025-09-11	80	f
1428	210	59	2025-09-11	90	f
1429	211	58	2025-09-11	80	f
1430	211	59	2025-09-11	90	f
1431	212	58	2025-09-11	40	f
1432	212	59	2025-09-11	85	f
1433	213	58	2025-09-11	40	f
1434	213	59	2025-09-11	75	f
1435	214	58	2025-09-11	60	f
1436	214	59	2025-09-11	90	f
1437	215	58	2025-09-11	60	f
1438	215	59	2025-09-11	85	f
1439	216	58	2025-09-11	100	f
1440	217	58	2025-09-11	60	f
1441	217	59	2025-09-11	90	f
1442	218	26	2025-09-11	100	f
1443	218	58	2025-09-11	40	f
1444	218	59	2025-09-11	75	f
1445	100	97	2025-10-02	97	f
1446	32	82	2025-10-02	97	f
1447	32	80	2025-10-02	98	f
1448	33	71	2025-10-02	95	f
1449	33	72	2025-10-02	90	f
1450	230	55	2025-10-02	100	f
1451	231	55	2025-10-02	95	f
1452	231	56	2025-10-02	74	f
1453	230	56	2025-10-02	98	f
1454	4	154	2025-10-02	100	f
1455	142	58	2025-10-16	100	f
1456	142	59	2025-10-16	100	f
1457	142	60	2025-10-16	100	f
1458	142	61	2025-10-16	100	f
1459	142	62	2025-10-16	100	f
1460	142	63	2025-10-16	100	f
1461	142	64	2025-10-16	100	f
1462	55	66	2025-10-16	100	f
1463	55	67	2025-10-16	95	f
1464	55	68	2025-10-16	100	f
1465	55	69	2025-10-16	90	f
1466	55	70	2025-10-16	100	f
1467	55	72	2025-10-16	100	f
\.


--
-- Name: estudiantes_cursos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('estudiantes_cursos_id_seq', 1467, true);


--
-- Name: estudiantes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('estudiantes_id_seq', 231, true);


--
-- Data for Name: estudiantes_programas; Type: TABLE DATA; Schema: public; Owner: emmaus
--

COPY estudiantes_programas (id, estudiante_id, programa_id, fecha_inscripcion) FROM stdin;
\.


--
-- Name: estudiantes_programas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('estudiantes_programas_id_seq', 1, false);


--
-- Data for Name: niveles; Type: TABLE DATA; Schema: public; Owner: emmaus
--

COPY niveles (id, nombre) FROM stdin;
1	Serie 1
2	Serie 2
3	Única
4	Nivel 2
5	Nivel 3
\.


--
-- Name: niveles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('niveles_id_seq', 5, true);


--
-- Data for Name: niveles_programas; Type: TABLE DATA; Schema: public; Owner: emmaus
--

COPY niveles_programas (id, programa_id, nombre, version) FROM stdin;
4	1	Nivel 1	1
5	1	Nivel 2	1
6	1	Nivel 3	1
7	2	Nivel 1	1
8	2	Nivel 2	1
9	2	Nivel 3	1
10	3	Nivel 1	1
11	3	Nivel 2	1
12	3	Nivel 3	1
13	4	Nivel 1	1
14	4	Nivel 2	1
15	4	Nivel 3	1
16	5	Nivel 1	1
17	5	Nivel 2	1
18	6	Nivel 1	1
19	6	Nivel 2	1
20	7	Pre-Escolar	1
21	7	Primero	1
22	7	Segundo	1
23	7	Tercero	1
24	7	Cuarto	1
25	7	Quinto	1
26	7	Séptimo	1
27	7	Noveno	1
28	7	Décimo	1
29	7	Onceavo	1
\.


--
-- Name: niveles_programas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('niveles_programas_id_seq', 29, true);


--
-- Data for Name: observaciones_estudiantes; Type: TABLE DATA; Schema: public; Owner: emmaus
--

COPY observaciones_estudiantes (id, estudiante_id, observacion, fecha, usuario_id, tipo) FROM stdin;
1	92	Jose antonio fallecio el 20 mayo 2025	2025-08-26 11:29:28.695755	16	General
2	33	SE ENTREGA DIPLOMA PAIS LLAMADO EL CIELO 02/10/2025	2025-10-02 15:39:08.924423	14	General
\.


--
-- Name: observaciones_estudiantes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('observaciones_estudiantes_id_seq', 2, true);


--
-- Data for Name: programas; Type: TABLE DATA; Schema: public; Owner: emmaus
--

COPY programas (id, nombre, descripcion, current_version, created_at, updated_at) FROM stdin;
1	Programa Niños (nuevo)		1	2025-10-22 21:19:06.65804	2025-10-22 21:19:06.65804
2	Programa Niños (antiguo)		1	2025-10-22 21:19:06.65804	2025-10-22 21:19:06.65804
3	Programa Adolescentes (nuevo)		1	2025-10-22 21:19:06.65804	2025-10-22 21:19:06.65804
4	Programa Adolescentes (Antiguo)		1	2025-10-22 21:19:06.65804	2025-10-22 21:19:06.65804
5	Programa Adultos (nuevo)		1	2025-10-22 21:19:06.65804	2025-10-22 21:19:06.65804
6	Programa Adultos (Antiguo)		1	2025-10-22 21:19:06.65804	2025-10-22 21:19:06.65804
7	Programa Colegios		1	2025-10-22 21:19:06.65804	2025-10-22 21:19:06.65804
\.


--
-- Data for Name: programas_asignaciones; Type: TABLE DATA; Schema: public; Owner: emmaus
--

COPY programas_asignaciones (id, programa_id, estudiante_id, contacto_id, fecha_asignacion, version, activo) FROM stdin;
\.


--
-- Name: programas_asignaciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('programas_asignaciones_id_seq', 1, false);


--
-- Data for Name: programas_cursos; Type: TABLE DATA; Schema: public; Owner: emmaus
--

COPY programas_cursos (id, programa_id, curso_id, nivel_id, consecutivo, version) FROM stdin;
129	2	26	7	1	1
130	2	27	7	2	1
131	2	28	7	3	1
132	2	29	7	4	1
133	2	30	7	5	1
134	2	31	7	6	1
135	2	32	7	7	1
136	2	33	7	8	1
137	2	34	7	9	1
138	2	35	7	10	1
139	2	36	7	11	1
140	2	37	7	12	1
141	2	38	7	13	1
142	2	39	7	14	1
143	2	40	7	15	1
144	2	41	7	16	1
145	2	42	7	17	1
146	2	43	7	18	1
147	2	44	7	19	1
148	2	45	7	20	1
149	2	46	7	21	1
150	2	47	7	22	1
151	2	48	7	23	1
152	2	55	7	24	1
153	2	56	7	25	1
154	2	57	7	26	1
155	2	49	8	27	1
156	2	50	8	28	1
157	2	51	8	29	1
158	2	52	8	30	1
159	2	53	8	31	1
160	2	54	8	32	1
161	2	58	8	33	1
162	2	59	8	34	1
163	2	60	8	35	1
164	2	61	8	36	1
165	2	62	8	37	1
166	2	63	8	38	1
167	2	64	8	39	1
168	2	65	8	40	1
169	2	73	8	41	1
170	2	74	9	42	1
171	2	75	9	43	1
172	2	76	9	44	1
173	2	77	9	45	1
174	2	78	9	46	1
175	2	95	9	47	1
176	2	96	9	48	1
177	2	97	9	49	1
178	2	98	9	50	1
179	2	99	9	51	1
180	2	100	9	52	1
181	2	101	9	53	1
182	2	102	9	54	1
183	2	103	9	55	1
184	2	104	9	56	1
185	2	105	9	57	1
186	2	106	9	58	1
187	2	107	9	59	1
188	2	116	9	60	1
189	2	117	9	61	1
190	2	118	9	62	1
191	2	119	9	63	1
192	2	120	9	64	1
65	1	26	4	1	1
66	1	27	4	2	1
67	1	28	4	3	1
68	1	29	4	4	1
69	1	30	4	5	1
70	1	31	4	6	1
71	1	32	4	7	1
72	1	33	4	8	1
73	1	34	4	9	1
74	1	35	4	10	1
75	1	36	4	11	1
76	1	37	4	12	1
77	1	38	4	13	1
78	1	39	4	14	1
79	1	40	4	15	1
80	1	41	4	16	1
81	1	42	4	17	1
82	1	43	4	18	1
83	1	44	4	19	1
84	1	45	4	20	1
85	1	46	4	21	1
86	1	47	4	22	1
87	1	48	4	23	1
88	1	55	4	24	1
89	1	56	4	25	1
90	1	57	4	26	1
91	1	49	5	27	1
92	1	50	5	28	1
93	1	51	5	29	1
94	1	52	5	30	1
95	1	53	5	31	1
96	1	54	5	32	1
97	1	66	5	33	1
98	1	67	5	34	1
99	1	68	5	35	1
100	1	69	5	36	1
101	1	70	5	37	1
102	1	71	5	38	1
103	1	72	5	39	1
104	1	73	5	40	1
105	1	79	6	41	1
106	1	80	6	42	1
107	1	81	6	43	1
108	1	82	6	44	1
109	1	83	6	45	1
110	1	84	6	46	1
111	1	85	6	47	1
112	1	86	6	48	1
113	1	87	6	49	1
114	1	88	6	50	1
115	1	89	6	51	1
116	1	90	6	52	1
117	1	91	6	53	1
118	1	92	6	54	1
119	1	93	6	55	1
120	1	94	6	56	1
121	1	108	6	57	1
122	1	109	6	58	1
123	1	110	6	59	1
124	1	111	6	60	1
125	1	112	6	61	1
126	1	113	6	62	1
127	1	114	6	63	1
128	1	115	6	64	1
193	2	121	9	65	1
194	2	122	9	66	1
195	2	123	9	67	1
196	2	124	9	68	1
197	2	125	9	69	1
198	2	126	9	70	1
199	2	127	9	71	1
200	3	38	10	1	1
201	3	39	10	2	1
202	3	40	10	3	1
203	3	41	10	4	1
204	3	42	10	5	1
205	3	43	10	6	1
206	3	44	10	7	1
207	3	45	10	8	1
208	3	46	10	9	1
209	3	47	10	10	1
210	3	48	10	11	1
211	3	49	10	12	1
212	3	50	10	13	1
213	3	51	10	14	1
214	3	52	10	15	1
215	3	53	10	16	1
216	3	54	10	17	1
217	3	55	10	18	1
218	3	56	10	19	1
219	3	57	10	20	1
220	3	66	10	21	1
221	3	67	10	22	1
222	3	68	10	23	1
223	3	69	10	24	1
224	3	70	10	25	1
225	3	71	10	26	1
226	3	64	10	27	1
227	3	72	10	28	1
228	3	73	10	29	1
229	3	79	11	30	1
230	3	80	11	31	1
231	3	81	11	32	1
232	3	82	11	33	1
233	3	83	11	34	1
234	3	84	11	35	1
235	3	85	11	36	1
236	3	86	11	37	1
237	3	87	11	38	1
238	3	88	11	39	1
239	3	89	11	40	1
240	3	90	11	41	1
241	3	91	11	42	1
242	3	92	11	43	1
243	3	93	11	44	1
244	3	94	11	45	1
245	3	108	11	46	1
246	3	109	11	47	1
247	3	110	11	48	1
248	3	111	11	49	1
249	3	112	11	50	1
250	3	113	11	51	1
251	3	114	11	52	1
252	3	115	11	53	1
253	3	141	12	54	1
254	3	142	12	55	1
255	3	143	12	56	1
256	3	144	12	57	1
257	3	145	12	58	1
258	3	146	12	59	1
259	3	128	12	60	1
260	3	129	12	61	1
261	3	130	12	62	1
262	3	134	12	63	1
263	3	135	12	64	1
264	3	136	12	65	1
265	3	137	12	66	1
266	3	138	12	67	1
267	3	139	12	68	1
268	3	140	12	69	1
269	3	147	12	70	1
270	3	148	12	71	1
271	3	149	12	72	1
272	3	131	12	73	1
273	3	132	12	74	1
274	3	133	12	75	1
275	4	38	13	1	1
276	4	39	13	2	1
277	4	40	13	3	1
278	4	41	13	4	1
279	4	42	13	5	1
280	4	43	13	6	1
281	4	44	13	7	1
282	4	45	13	8	1
283	4	46	13	9	1
284	4	47	13	10	1
285	4	48	13	11	1
286	4	49	13	12	1
287	4	50	13	13	1
288	4	51	13	14	1
289	4	52	13	15	1
290	4	53	13	16	1
291	4	54	13	17	1
292	4	55	13	18	1
293	4	56	13	19	1
294	4	57	13	20	1
295	4	58	13	21	1
296	4	59	13	22	1
297	4	60	13	23	1
298	4	61	13	24	1
299	4	62	13	25	1
300	4	63	13	26	1
301	4	64	13	27	1
302	4	65	13	28	1
303	4	73	13	29	1
304	4	74	14	30	1
305	4	75	14	31	1
306	4	76	14	32	1
307	4	77	14	33	1
308	4	78	14	34	1
309	4	95	14	35	1
310	4	96	14	36	1
311	4	97	14	37	1
312	4	98	14	38	1
313	4	99	14	39	1
314	4	100	14	40	1
315	4	101	14	41	1
316	4	102	14	42	1
317	4	103	14	43	1
318	4	104	14	44	1
319	4	105	14	45	1
320	4	106	14	46	1
321	4	107	14	47	1
322	4	116	14	48	1
323	4	117	14	49	1
324	4	118	14	50	1
325	4	119	14	51	1
326	4	120	14	52	1
327	4	121	14	53	1
328	4	122	14	54	1
329	4	123	14	55	1
330	4	124	14	56	1
331	4	125	14	57	1
332	4	126	14	58	1
333	4	127	14	59	1
334	4	141	15	60	1
335	4	142	15	61	1
336	4	143	15	62	1
337	4	144	15	63	1
338	4	145	15	64	1
339	4	146	15	65	1
340	4	128	15	66	1
341	4	129	15	67	1
342	4	130	15	68	1
343	4	134	15	69	1
344	4	135	15	70	1
345	4	136	15	71	1
346	4	137	15	72	1
347	4	138	15	73	1
348	4	139	15	74	1
349	4	140	15	75	1
350	4	147	15	76	1
351	4	148	15	77	1
352	4	149	15	78	1
353	4	131	15	79	1
354	4	132	15	80	1
355	4	133	15	81	1
356	5	55	16	1	1
357	5	56	16	2	1
358	5	57	16	3	1
359	5	73	16	4	1
360	5	66	16	5	1
361	5	67	16	6	1
362	5	68	16	7	1
363	5	69	16	8	1
364	5	70	16	9	1
365	5	71	16	10	1
366	5	72	16	11	1
367	5	79	16	12	1
368	5	80	16	13	1
369	5	81	16	14	1
370	5	82	16	15	1
371	5	83	16	16	1
372	5	84	16	17	1
373	5	85	16	18	1
374	5	86	16	19	1
375	5	87	16	20	1
376	5	88	16	21	1
377	5	89	16	22	1
378	5	90	16	23	1
379	5	91	16	24	1
380	5	92	16	25	1
381	5	93	16	26	1
382	5	94	16	27	1
383	5	108	17	28	1
384	5	109	17	29	1
385	5	110	17	30	1
386	5	111	17	31	1
387	5	112	17	32	1
388	5	113	17	33	1
389	5	114	17	34	1
390	5	115	17	35	1
391	5	128	17	36	1
392	5	129	17	37	1
393	5	130	17	38	1
394	5	134	17	39	1
395	5	135	17	40	1
396	5	136	17	41	1
397	5	137	17	42	1
398	5	138	17	43	1
399	5	139	17	44	1
400	5	140	17	45	1
401	5	147	17	46	1
402	5	148	17	47	1
403	5	149	17	48	1
404	5	131	17	49	1
405	5	132	17	50	1
406	5	133	17	51	1
407	6	55	18	1	1
408	6	56	18	2	1
409	6	57	18	3	1
410	6	73	18	4	1
411	6	58	18	5	1
412	6	59	18	6	1
413	6	60	18	7	1
414	6	61	18	8	1
415	6	62	18	9	1
416	6	63	18	10	1
417	6	64	18	11	1
418	6	65	18	12	1
419	6	74	18	13	1
420	6	75	18	14	1
421	6	76	18	15	1
422	6	77	18	16	1
423	6	78	18	17	1
424	6	95	18	18	1
425	6	96	18	19	1
426	6	97	18	20	1
427	6	98	18	21	1
428	6	99	18	22	1
429	6	100	18	23	1
430	6	101	18	24	1
431	6	102	18	25	1
432	6	103	18	26	1
433	6	104	18	27	1
434	6	105	18	28	1
435	6	106	18	29	1
436	6	107	18	30	1
437	6	116	19	31	1
438	6	117	19	32	1
439	6	118	19	33	1
440	6	119	19	34	1
441	6	120	19	35	1
442	6	121	19	36	1
443	6	122	19	37	1
444	6	123	19	38	1
445	6	124	19	39	1
446	6	125	19	40	1
447	6	126	19	41	1
448	6	127	19	42	1
449	6	128	19	43	1
450	6	129	19	44	1
451	6	130	19	45	1
452	6	134	19	46	1
453	6	135	19	47	1
454	6	136	19	48	1
455	6	137	19	49	1
456	6	138	19	50	1
457	6	139	19	51	1
458	6	140	19	52	1
459	6	147	19	53	1
460	6	148	19	54	1
461	6	149	19	55	1
462	6	131	19	56	1
463	6	132	19	57	1
464	6	133	19	58	1
465	7	26	20	1	1
466	7	27	20	2	1
467	7	28	20	3	1
468	7	29	20	4	1
469	7	30	20	5	1
470	7	31	20	6	1
471	7	32	20	7	1
472	7	33	20	8	1
473	7	34	20	9	1
474	7	35	20	10	1
475	7	36	20	11	1
476	7	37	20	12	1
477	7	38	21	13	1
478	7	39	21	14	1
479	7	40	21	15	1
480	7	41	21	16	1
481	7	42	21	17	1
482	7	43	21	18	1
483	7	44	21	19	1
484	7	45	21	20	1
485	7	46	21	21	1
486	7	47	21	22	1
487	7	48	21	23	1
488	7	55	21	24	1
489	7	56	21	25	1
490	7	57	21	26	1
491	7	49	22	27	1
492	7	50	22	28	1
493	7	51	22	29	1
494	7	52	22	30	1
495	7	53	22	31	1
496	7	54	22	32	1
497	7	66	22	33	1
498	7	67	22	34	1
499	7	68	22	35	1
500	7	69	22	36	1
501	7	70	22	37	1
502	7	71	22	38	1
503	7	72	22	39	1
504	7	83	23	40	1
505	7	84	23	41	1
506	7	85	23	42	1
507	7	86	23	43	1
508	7	87	23	44	1
509	7	88	23	45	1
510	7	89	23	46	1
511	7	90	23	47	1
512	7	91	23	48	1
513	7	92	23	49	1
514	7	93	23	50	1
515	7	94	23	51	1
516	7	108	24	52	1
517	7	109	24	53	1
518	7	110	24	54	1
519	7	111	24	55	1
520	7	112	24	56	1
521	7	113	24	57	1
522	7	114	24	58	1
523	7	115	24	59	1
524	7	79	25	60	1
525	7	80	25	61	1
526	7	81	25	62	1
527	7	82	25	63	1
528	7	73	25	64	1
529	7	128	25	65	1
530	7	129	25	66	1
531	7	130	25	67	1
532	7	141	26	68	1
533	7	142	26	69	1
534	7	143	26	70	1
535	7	144	26	71	1
536	7	145	26	72	1
537	7	146	26	73	1
538	7	135	27	74	1
539	7	137	28	75	1
540	7	140	29	76	1
\.


--
-- Name: programas_cursos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('programas_cursos_id_seq', 540, true);


--
-- Name: programas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('programas_id_seq', 7, true);


--
-- Data for Name: programas_prerequisitos; Type: TABLE DATA; Schema: public; Owner: emmaus
--

COPY programas_prerequisitos (id, programa_id, prerequisito_id) FROM stdin;
\.


--
-- Name: programas_prerequisitos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('programas_prerequisitos_id_seq', 1, false);


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: emmaus
--

COPY users (id, username, password) FROM stdin;
2	daniel.vanegas	$2y$10$AhNzu9N3hPnA9s2863sjcO5/3MKIWKSXpHqQz3Xh63vxHkX1FKTCG
\.


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('users_id_seq', 2, true);


--
-- Name: actas_diplomas_numero_acta_key; Type: CONSTRAINT; Schema: public; Owner: emmaus; Tablespace: 
--

ALTER TABLE ONLY actas_diplomas
    ADD CONSTRAINT actas_diplomas_numero_acta_key UNIQUE (numero_acta);


--
-- Name: actas_diplomas_pkey; Type: CONSTRAINT; Schema: public; Owner: emmaus; Tablespace: 
--

ALTER TABLE ONLY actas_diplomas
    ADD CONSTRAINT actas_diplomas_pkey PRIMARY KEY (id);


--
-- Name: contactos_pkey; Type: CONSTRAINT; Schema: public; Owner: emmaus; Tablespace: 
--

ALTER TABLE ONLY contactos
    ADD CONSTRAINT contactos_pkey PRIMARY KEY (id);


--
-- Name: cursos_pkey; Type: CONSTRAINT; Schema: public; Owner: emmaus; Tablespace: 
--

ALTER TABLE ONLY cursos
    ADD CONSTRAINT cursos_pkey PRIMARY KEY (id);


--
-- Name: diplomas_entregados_pkey; Type: CONSTRAINT; Schema: public; Owner: emmaus; Tablespace: 
--

ALTER TABLE ONLY diplomas_entregados
    ADD CONSTRAINT diplomas_entregados_pkey PRIMARY KEY (id);


--
-- Name: estudiantes_cursos_pkey; Type: CONSTRAINT; Schema: public; Owner: emmaus; Tablespace: 
--

ALTER TABLE ONLY estudiantes_cursos
    ADD CONSTRAINT estudiantes_cursos_pkey PRIMARY KEY (id);


--
-- Name: estudiantes_pkey; Type: CONSTRAINT; Schema: public; Owner: emmaus; Tablespace: 
--

ALTER TABLE ONLY estudiantes
    ADD CONSTRAINT estudiantes_pkey PRIMARY KEY (id);


--
-- Name: estudiantes_programas_estudiante_id_programa_id_key; Type: CONSTRAINT; Schema: public; Owner: emmaus; Tablespace: 
--

ALTER TABLE ONLY estudiantes_programas
    ADD CONSTRAINT estudiantes_programas_estudiante_id_programa_id_key UNIQUE (estudiante_id, programa_id);


--
-- Name: estudiantes_programas_pkey; Type: CONSTRAINT; Schema: public; Owner: emmaus; Tablespace: 
--

ALTER TABLE ONLY estudiantes_programas
    ADD CONSTRAINT estudiantes_programas_pkey PRIMARY KEY (id);


--
-- Name: niveles_pkey; Type: CONSTRAINT; Schema: public; Owner: emmaus; Tablespace: 
--

ALTER TABLE ONLY niveles
    ADD CONSTRAINT niveles_pkey PRIMARY KEY (id);


--
-- Name: niveles_programas_pkey; Type: CONSTRAINT; Schema: public; Owner: emmaus; Tablespace: 
--

ALTER TABLE ONLY niveles_programas
    ADD CONSTRAINT niveles_programas_pkey PRIMARY KEY (id);


--
-- Name: observaciones_estudiantes_pkey; Type: CONSTRAINT; Schema: public; Owner: emmaus; Tablespace: 
--

ALTER TABLE ONLY observaciones_estudiantes
    ADD CONSTRAINT observaciones_estudiantes_pkey PRIMARY KEY (id);


--
-- Name: programas_asignaciones_pkey; Type: CONSTRAINT; Schema: public; Owner: emmaus; Tablespace: 
--

ALTER TABLE ONLY programas_asignaciones
    ADD CONSTRAINT programas_asignaciones_pkey PRIMARY KEY (id);


--
-- Name: programas_cursos_pkey; Type: CONSTRAINT; Schema: public; Owner: emmaus; Tablespace: 
--

ALTER TABLE ONLY programas_cursos
    ADD CONSTRAINT programas_cursos_pkey PRIMARY KEY (id);


--
-- Name: programas_pkey; Type: CONSTRAINT; Schema: public; Owner: emmaus; Tablespace: 
--

ALTER TABLE ONLY programas
    ADD CONSTRAINT programas_pkey PRIMARY KEY (id);


--
-- Name: programas_prerequisitos_pkey; Type: CONSTRAINT; Schema: public; Owner: emmaus; Tablespace: 
--

ALTER TABLE ONLY programas_prerequisitos
    ADD CONSTRAINT programas_prerequisitos_pkey PRIMARY KEY (id);


--
-- Name: programas_prerequisitos_programa_id_prerequisito_id_key; Type: CONSTRAINT; Schema: public; Owner: emmaus; Tablespace: 
--

ALTER TABLE ONLY programas_prerequisitos
    ADD CONSTRAINT programas_prerequisitos_programa_id_prerequisito_id_key UNIQUE (programa_id, prerequisito_id);


--
-- Name: users_pkey; Type: CONSTRAINT; Schema: public; Owner: emmaus; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: users_username_key; Type: CONSTRAINT; Schema: public; Owner: emmaus; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_username_key UNIQUE (username);


--
-- Name: cursos_deleted_active_idx; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE INDEX cursos_deleted_active_idx ON cursos USING btree (id) WHERE (deleted_at IS NULL);


--
-- Name: idx_actas_contacto; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE INDEX idx_actas_contacto ON actas_diplomas USING btree (contacto_id);


--
-- Name: idx_actas_fecha; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE INDEX idx_actas_fecha ON actas_diplomas USING btree (fecha_acta DESC);


--
-- Name: idx_actas_numero; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE INDEX idx_actas_numero ON actas_diplomas USING btree (numero_acta);


--
-- Name: idx_contacto_id; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE INDEX idx_contacto_id ON programas_asignaciones USING btree (contacto_id);


--
-- Name: idx_diplomas_acta; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE INDEX idx_diplomas_acta ON diplomas_entregados USING btree (acta_id);


--
-- Name: idx_diplomas_contacto; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE INDEX idx_diplomas_contacto ON diplomas_entregados USING btree (contacto_id);


--
-- Name: idx_diplomas_emision; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE INDEX idx_diplomas_emision ON diplomas_entregados USING btree (fecha_emision);


--
-- Name: idx_diplomas_entrega; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE INDEX idx_diplomas_entrega ON diplomas_entregados USING btree (fecha_entrega);


--
-- Name: idx_diplomas_estudiante; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE INDEX idx_diplomas_estudiante ON diplomas_entregados USING btree (estudiante_id);


--
-- Name: idx_diplomas_nivel; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE INDEX idx_diplomas_nivel ON diplomas_entregados USING btree (nivel_id);


--
-- Name: idx_diplomas_pendientes; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE INDEX idx_diplomas_pendientes ON diplomas_entregados USING btree (fecha_entrega) WHERE (fecha_entrega IS NULL);


--
-- Name: idx_diplomas_programa; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE INDEX idx_diplomas_programa ON diplomas_entregados USING btree (programa_id);


--
-- Name: idx_diplomas_tipo; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE INDEX idx_diplomas_tipo ON diplomas_entregados USING btree (tipo);


--
-- Name: idx_estudiante_id; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE INDEX idx_estudiante_id ON programas_asignaciones USING btree (estudiante_id);


--
-- Name: idx_programa_id; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE INDEX idx_programa_id ON programas_asignaciones USING btree (programa_id);


--
-- Name: idx_programas_asig_contacto_activo; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE INDEX idx_programas_asig_contacto_activo ON programas_asignaciones USING btree (contacto_id, activo);


--
-- Name: idx_programas_asignaciones_activo; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE INDEX idx_programas_asignaciones_activo ON programas_asignaciones USING btree (activo) WHERE (activo = true);


--
-- Name: idx_unique_contacto_programa; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE UNIQUE INDEX idx_unique_contacto_programa ON programas_asignaciones USING btree (programa_id, contacto_id) WHERE (contacto_id IS NOT NULL);


--
-- Name: idx_unique_estudiante_programa; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE UNIQUE INDEX idx_unique_estudiante_programa ON programas_asignaciones USING btree (programa_id, estudiante_id) WHERE (estudiante_id IS NOT NULL);


--
-- Name: niveles_programas_programa_version_idx; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE INDEX niveles_programas_programa_version_idx ON niveles_programas USING btree (programa_id, version);


--
-- Name: programas_asignaciones_programa_version_idx; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE INDEX programas_asignaciones_programa_version_idx ON programas_asignaciones USING btree (programa_id, version);


--
-- Name: programas_cursos_prog_ver_cons_uniq; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE UNIQUE INDEX programas_cursos_prog_ver_cons_uniq ON programas_cursos USING btree (programa_id, version, consecutivo);


--
-- Name: programas_cursos_programa_version_idx; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE INDEX programas_cursos_programa_version_idx ON programas_cursos USING btree (programa_id, version);


--
-- Name: uq_diploma_programa_estudiante; Type: INDEX; Schema: public; Owner: emmaus; Tablespace: 
--

CREATE UNIQUE INDEX uq_diploma_programa_estudiante ON diplomas_entregados USING btree (tipo, programa_id, (COALESCE(nivel_id, 0)), estudiante_id) WHERE (estudiante_id IS NOT NULL);


--
-- Name: actas_diplomas_contacto_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY actas_diplomas
    ADD CONSTRAINT actas_diplomas_contacto_id_fkey FOREIGN KEY (contacto_id) REFERENCES contactos(id) ON DELETE SET NULL;


--
-- Name: cursos_nivel_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY cursos
    ADD CONSTRAINT cursos_nivel_id_fkey FOREIGN KEY (nivel_id) REFERENCES niveles(id);


--
-- Name: diplomas_entregados_contacto_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY diplomas_entregados
    ADD CONSTRAINT diplomas_entregados_contacto_id_fkey FOREIGN KEY (contacto_id) REFERENCES contactos(id) ON DELETE CASCADE;


--
-- Name: diplomas_entregados_estudiante_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY diplomas_entregados
    ADD CONSTRAINT diplomas_entregados_estudiante_id_fkey FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE;


--
-- Name: diplomas_entregados_nivel_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY diplomas_entregados
    ADD CONSTRAINT diplomas_entregados_nivel_id_fkey FOREIGN KEY (nivel_id) REFERENCES niveles_programas(id) ON DELETE SET NULL;


--
-- Name: diplomas_entregados_programa_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY diplomas_entregados
    ADD CONSTRAINT diplomas_entregados_programa_id_fkey FOREIGN KEY (programa_id) REFERENCES programas(id) ON DELETE CASCADE;


--
-- Name: estudiantes_cursos_curso_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY estudiantes_cursos
    ADD CONSTRAINT estudiantes_cursos_curso_id_fkey FOREIGN KEY (curso_id) REFERENCES cursos(id);


--
-- Name: estudiantes_cursos_estudiante_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY estudiantes_cursos
    ADD CONSTRAINT estudiantes_cursos_estudiante_id_fkey FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id);


--
-- Name: estudiantes_id_contacto_fkey; Type: FK CONSTRAINT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY estudiantes
    ADD CONSTRAINT estudiantes_id_contacto_fkey FOREIGN KEY (id_contacto) REFERENCES contactos(id);


--
-- Name: estudiantes_programas_estudiante_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY estudiantes_programas
    ADD CONSTRAINT estudiantes_programas_estudiante_id_fkey FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE;


--
-- Name: estudiantes_programas_programa_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY estudiantes_programas
    ADD CONSTRAINT estudiantes_programas_programa_id_fkey FOREIGN KEY (programa_id) REFERENCES programas(id) ON DELETE CASCADE;


--
-- Name: fk_contacto; Type: FK CONSTRAINT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY programas_asignaciones
    ADD CONSTRAINT fk_contacto FOREIGN KEY (contacto_id) REFERENCES contactos(id) ON DELETE CASCADE;


--
-- Name: fk_diplomas_acta; Type: FK CONSTRAINT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY diplomas_entregados
    ADD CONSTRAINT fk_diplomas_acta FOREIGN KEY (acta_id) REFERENCES actas_diplomas(id) ON DELETE SET NULL;


--
-- Name: fk_estudiante; Type: FK CONSTRAINT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY programas_asignaciones
    ADD CONSTRAINT fk_estudiante FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE;


--
-- Name: fk_observaciones_estudiante; Type: FK CONSTRAINT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY observaciones_estudiantes
    ADD CONSTRAINT fk_observaciones_estudiante FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE;


--
-- Name: fk_programa; Type: FK CONSTRAINT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY programas_asignaciones
    ADD CONSTRAINT fk_programa FOREIGN KEY (programa_id) REFERENCES programas(id) ON DELETE CASCADE;


--
-- Name: niveles_programas_programa_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY niveles_programas
    ADD CONSTRAINT niveles_programas_programa_id_fkey FOREIGN KEY (programa_id) REFERENCES programas(id) ON DELETE CASCADE;


--
-- Name: programas_cursos_curso_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY programas_cursos
    ADD CONSTRAINT programas_cursos_curso_id_fkey FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE;


--
-- Name: programas_cursos_nivel_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY programas_cursos
    ADD CONSTRAINT programas_cursos_nivel_id_fkey FOREIGN KEY (nivel_id) REFERENCES niveles_programas(id) ON DELETE SET NULL;


--
-- Name: programas_cursos_programa_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY programas_cursos
    ADD CONSTRAINT programas_cursos_programa_id_fkey FOREIGN KEY (programa_id) REFERENCES programas(id) ON DELETE CASCADE;


--
-- Name: programas_prerequisitos_prerequisito_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY programas_prerequisitos
    ADD CONSTRAINT programas_prerequisitos_prerequisito_id_fkey FOREIGN KEY (prerequisito_id) REFERENCES programas(id) ON DELETE CASCADE;


--
-- Name: programas_prerequisitos_programa_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: emmaus
--

ALTER TABLE ONLY programas_prerequisitos
    ADD CONSTRAINT programas_prerequisitos_programa_id_fkey FOREIGN KEY (programa_id) REFERENCES programas(id) ON DELETE CASCADE;


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- Name: actas_diplomas; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE actas_diplomas FROM PUBLIC;
REVOKE ALL ON TABLE actas_diplomas FROM emmaus;
GRANT ALL ON TABLE actas_diplomas TO emmaus;
GRANT ALL ON TABLE actas_diplomas TO emmaus_source_of_light;


--
-- Name: actas_diplomas_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE actas_diplomas_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE actas_diplomas_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE actas_diplomas_id_seq TO emmaus;
GRANT ALL ON SEQUENCE actas_diplomas_id_seq TO emmaus_source_of_light;


--
-- Name: contactos; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE contactos FROM PUBLIC;
REVOKE ALL ON TABLE contactos FROM emmaus;
GRANT ALL ON TABLE contactos TO emmaus;
GRANT ALL ON TABLE contactos TO emmaus_estudiantes;
GRANT ALL ON TABLE contactos TO emmaus_source_of_light;
GRANT ALL ON TABLE contactos TO emmaus_admin;
GRANT ALL ON TABLE contactos TO emmaus_admin_sol;


--
-- Name: contactos_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE contactos_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE contactos_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE contactos_id_seq TO emmaus;
GRANT ALL ON SEQUENCE contactos_id_seq TO emmaus_admin;
GRANT ALL ON SEQUENCE contactos_id_seq TO emmaus_source_of_light;


--
-- Name: cursos; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE cursos FROM PUBLIC;
REVOKE ALL ON TABLE cursos FROM emmaus;
GRANT ALL ON TABLE cursos TO emmaus;
GRANT ALL ON TABLE cursos TO emmaus_estudiantes;
GRANT ALL ON TABLE cursos TO emmaus_source_of_light;
GRANT ALL ON TABLE cursos TO emmaus_admin;
GRANT ALL ON TABLE cursos TO emmaus_admin_sol;


--
-- Name: cursos_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE cursos_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE cursos_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE cursos_id_seq TO emmaus;
GRANT ALL ON SEQUENCE cursos_id_seq TO emmaus_admin;
GRANT ALL ON SEQUENCE cursos_id_seq TO emmaus_source_of_light;


--
-- Name: diplomas_entregados; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE diplomas_entregados FROM PUBLIC;
REVOKE ALL ON TABLE diplomas_entregados FROM emmaus;
GRANT ALL ON TABLE diplomas_entregados TO emmaus;
GRANT ALL ON TABLE diplomas_entregados TO emmaus_source_of_light;


--
-- Name: diplomas_entregados_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE diplomas_entregados_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE diplomas_entregados_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE diplomas_entregados_id_seq TO emmaus;
GRANT ALL ON SEQUENCE diplomas_entregados_id_seq TO emmaus_source_of_light;


--
-- Name: estudiantes; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE estudiantes FROM PUBLIC;
REVOKE ALL ON TABLE estudiantes FROM emmaus;
GRANT ALL ON TABLE estudiantes TO emmaus;
GRANT ALL ON TABLE estudiantes TO emmaus_estudiantes;
GRANT ALL ON TABLE estudiantes TO emmaus_source_of_light;
GRANT ALL ON TABLE estudiantes TO emmaus_admin;
GRANT ALL ON TABLE estudiantes TO emmaus_admin_sol;


--
-- Name: estudiantes_cursos; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE estudiantes_cursos FROM PUBLIC;
REVOKE ALL ON TABLE estudiantes_cursos FROM emmaus;
GRANT ALL ON TABLE estudiantes_cursos TO emmaus;
GRANT ALL ON TABLE estudiantes_cursos TO emmaus_estudiantes;
GRANT ALL ON TABLE estudiantes_cursos TO emmaus_source_of_light;
GRANT ALL ON TABLE estudiantes_cursos TO emmaus_admin;
GRANT ALL ON TABLE estudiantes_cursos TO emmaus_admin_sol;


--
-- Name: estudiantes_cursos_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE estudiantes_cursos_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE estudiantes_cursos_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE estudiantes_cursos_id_seq TO emmaus;
GRANT ALL ON SEQUENCE estudiantes_cursos_id_seq TO emmaus_admin;
GRANT ALL ON SEQUENCE estudiantes_cursos_id_seq TO emmaus_source_of_light;


--
-- Name: estudiantes_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE estudiantes_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE estudiantes_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE estudiantes_id_seq TO emmaus;
GRANT ALL ON SEQUENCE estudiantes_id_seq TO emmaus_admin;
GRANT ALL ON SEQUENCE estudiantes_id_seq TO emmaus_source_of_light;


--
-- Name: estudiantes_programas; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE estudiantes_programas FROM PUBLIC;
REVOKE ALL ON TABLE estudiantes_programas FROM emmaus;
GRANT ALL ON TABLE estudiantes_programas TO emmaus;
GRANT ALL ON TABLE estudiantes_programas TO emmaus_source_of_light;
GRANT ALL ON TABLE estudiantes_programas TO emmaus_admin;
GRANT ALL ON TABLE estudiantes_programas TO emmaus_admin_sol;


--
-- Name: estudiantes_programas_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE estudiantes_programas_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE estudiantes_programas_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE estudiantes_programas_id_seq TO emmaus;
GRANT ALL ON SEQUENCE estudiantes_programas_id_seq TO emmaus_source_of_light;


--
-- Name: niveles; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE niveles FROM PUBLIC;
REVOKE ALL ON TABLE niveles FROM emmaus;
GRANT ALL ON TABLE niveles TO emmaus;
GRANT ALL ON TABLE niveles TO emmaus_estudiantes;
GRANT ALL ON TABLE niveles TO emmaus_source_of_light;
GRANT ALL ON TABLE niveles TO emmaus_admin;
GRANT ALL ON TABLE niveles TO emmaus_admin_sol;


--
-- Name: niveles_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE niveles_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE niveles_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE niveles_id_seq TO emmaus;
GRANT ALL ON SEQUENCE niveles_id_seq TO emmaus_admin;
GRANT ALL ON SEQUENCE niveles_id_seq TO emmaus_source_of_light;


--
-- Name: niveles_programas; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE niveles_programas FROM PUBLIC;
REVOKE ALL ON TABLE niveles_programas FROM emmaus;
GRANT ALL ON TABLE niveles_programas TO emmaus;
GRANT ALL ON TABLE niveles_programas TO emmaus_source_of_light;
GRANT ALL ON TABLE niveles_programas TO emmaus_estudiantes;
GRANT ALL ON TABLE niveles_programas TO emmaus_buc_estudiantes;
GRANT ALL ON TABLE niveles_programas TO emmaus_admin;
GRANT ALL ON TABLE niveles_programas TO emmaus_admin_sol;


--
-- Name: niveles_programas_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE niveles_programas_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE niveles_programas_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE niveles_programas_id_seq TO emmaus;
GRANT ALL ON SEQUENCE niveles_programas_id_seq TO emmaus_source_of_light;
GRANT ALL ON SEQUENCE niveles_programas_id_seq TO emmaus_estudiantes;
GRANT ALL ON SEQUENCE niveles_programas_id_seq TO emmaus_buc_estudiantes;
GRANT ALL ON SEQUENCE niveles_programas_id_seq TO emmaus_admin;


--
-- Name: observaciones_estudiantes; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE observaciones_estudiantes FROM PUBLIC;
REVOKE ALL ON TABLE observaciones_estudiantes FROM emmaus;
GRANT ALL ON TABLE observaciones_estudiantes TO emmaus;
GRANT ALL ON TABLE observaciones_estudiantes TO emmaus_source_of_light;
GRANT ALL ON TABLE observaciones_estudiantes TO emmaus_admin;
GRANT ALL ON TABLE observaciones_estudiantes TO emmaus_admin_sol;


--
-- Name: observaciones_estudiantes_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE observaciones_estudiantes_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE observaciones_estudiantes_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE observaciones_estudiantes_id_seq TO emmaus;
GRANT ALL ON SEQUENCE observaciones_estudiantes_id_seq TO emmaus_source_of_light;
GRANT ALL ON SEQUENCE observaciones_estudiantes_id_seq TO emmaus_pr_admin;


--
-- Name: programas; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE programas FROM PUBLIC;
REVOKE ALL ON TABLE programas FROM emmaus;
GRANT ALL ON TABLE programas TO emmaus;
GRANT ALL ON TABLE programas TO emmaus_source_of_light;
GRANT ALL ON TABLE programas TO emmaus_estudiantes;
GRANT ALL ON TABLE programas TO emmaus_buc_estudiantes;
GRANT ALL ON TABLE programas TO emmaus_admin;
GRANT ALL ON TABLE programas TO emmaus_admin_sol;


--
-- Name: programas_asignaciones; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE programas_asignaciones FROM PUBLIC;
REVOKE ALL ON TABLE programas_asignaciones FROM emmaus;
GRANT ALL ON TABLE programas_asignaciones TO emmaus;
GRANT ALL ON TABLE programas_asignaciones TO emmaus_source_of_light;
GRANT ALL ON TABLE programas_asignaciones TO emmaus_admin;
GRANT ALL ON TABLE programas_asignaciones TO emmaus_admin_sol;


--
-- Name: programas_asignaciones_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE programas_asignaciones_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE programas_asignaciones_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE programas_asignaciones_id_seq TO emmaus;
GRANT ALL ON SEQUENCE programas_asignaciones_id_seq TO emmaus_source_of_light;
GRANT ALL ON SEQUENCE programas_asignaciones_id_seq TO emmaus_admin;


--
-- Name: programas_cursos; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE programas_cursos FROM PUBLIC;
REVOKE ALL ON TABLE programas_cursos FROM emmaus;
GRANT ALL ON TABLE programas_cursos TO emmaus;
GRANT ALL ON TABLE programas_cursos TO emmaus_source_of_light;
GRANT ALL ON TABLE programas_cursos TO emmaus_estudiantes;
GRANT ALL ON TABLE programas_cursos TO emmaus_buc_estudiantes;
GRANT ALL ON TABLE programas_cursos TO emmaus_admin;
GRANT ALL ON TABLE programas_cursos TO emmaus_admin_sol;


--
-- Name: programas_cursos_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE programas_cursos_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE programas_cursos_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE programas_cursos_id_seq TO emmaus;
GRANT ALL ON SEQUENCE programas_cursos_id_seq TO emmaus_source_of_light;
GRANT ALL ON SEQUENCE programas_cursos_id_seq TO emmaus_estudiantes;
GRANT ALL ON SEQUENCE programas_cursos_id_seq TO emmaus_buc_estudiantes;
GRANT ALL ON SEQUENCE programas_cursos_id_seq TO emmaus_admin;


--
-- Name: programas_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE programas_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE programas_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE programas_id_seq TO emmaus;
GRANT ALL ON SEQUENCE programas_id_seq TO emmaus_source_of_light;
GRANT ALL ON SEQUENCE programas_id_seq TO emmaus_estudiantes;
GRANT ALL ON SEQUENCE programas_id_seq TO emmaus_buc_estudiantes;
GRANT ALL ON SEQUENCE programas_id_seq TO emmaus_admin;


--
-- Name: programas_prerequisitos; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE programas_prerequisitos FROM PUBLIC;
REVOKE ALL ON TABLE programas_prerequisitos FROM emmaus;
GRANT ALL ON TABLE programas_prerequisitos TO emmaus;
GRANT ALL ON TABLE programas_prerequisitos TO emmaus_source_of_light;
GRANT ALL ON TABLE programas_prerequisitos TO emmaus_estudiantes;
GRANT ALL ON TABLE programas_prerequisitos TO emmaus_buc_estudiantes;
GRANT ALL ON TABLE programas_prerequisitos TO emmaus_admin;
GRANT ALL ON TABLE programas_prerequisitos TO emmaus_admin_sol;


--
-- Name: programas_prerequisitos_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE programas_prerequisitos_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE programas_prerequisitos_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE programas_prerequisitos_id_seq TO emmaus;
GRANT ALL ON SEQUENCE programas_prerequisitos_id_seq TO emmaus_source_of_light;
GRANT ALL ON SEQUENCE programas_prerequisitos_id_seq TO emmaus_estudiantes;
GRANT ALL ON SEQUENCE programas_prerequisitos_id_seq TO emmaus_buc_estudiantes;
GRANT ALL ON SEQUENCE programas_prerequisitos_id_seq TO emmaus_admin;


--
-- Name: users; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE users FROM PUBLIC;
REVOKE ALL ON TABLE users FROM emmaus;
GRANT ALL ON TABLE users TO emmaus;
GRANT ALL ON TABLE users TO emmaus_estudiantes;
GRANT ALL ON TABLE users TO emmaus_source_of_light;
GRANT ALL ON TABLE users TO emmaus_admin;
GRANT ALL ON TABLE users TO emmaus_admin_sol;


--
-- Name: users_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE users_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE users_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE users_id_seq TO emmaus;
GRANT ALL ON SEQUENCE users_id_seq TO emmaus_admin;
GRANT ALL ON SEQUENCE users_id_seq TO emmaus_source_of_light;


--
-- Name: DEFAULT PRIVILEGES FOR SEQUENCES; Type: DEFAULT ACL; Schema: public; Owner: emmaus
--

ALTER DEFAULT PRIVILEGES FOR ROLE emmaus IN SCHEMA public REVOKE ALL ON SEQUENCES  FROM PUBLIC;
ALTER DEFAULT PRIVILEGES FOR ROLE emmaus IN SCHEMA public REVOKE ALL ON SEQUENCES  FROM emmaus;
ALTER DEFAULT PRIVILEGES FOR ROLE emmaus IN SCHEMA public GRANT ALL ON SEQUENCES  TO emmaus_source_of_light;


--
-- Name: DEFAULT PRIVILEGES FOR TABLES; Type: DEFAULT ACL; Schema: public; Owner: emmaus
--

ALTER DEFAULT PRIVILEGES FOR ROLE emmaus IN SCHEMA public REVOKE ALL ON TABLES  FROM PUBLIC;
ALTER DEFAULT PRIVILEGES FOR ROLE emmaus IN SCHEMA public REVOKE ALL ON TABLES  FROM emmaus;
ALTER DEFAULT PRIVILEGES FOR ROLE emmaus IN SCHEMA public GRANT ALL ON TABLES  TO emmaus_source_of_light;


--
-- PostgreSQL database dump complete
--
