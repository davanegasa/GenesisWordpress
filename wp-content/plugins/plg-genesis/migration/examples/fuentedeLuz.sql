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

--
-- Name: plg_set_updated_at(); Type: FUNCTION; Schema: public; Owner: emmaus
--

CREATE FUNCTION plg_set_updated_at() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    NEW.updated_at := NOW();
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.plg_set_updated_at() OWNER TO emmaus;

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
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now(),
    deleted_at timestamp with time zone
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
1	Maria Angelica Pacheco Galindo	Santuario	mapac1@hotmail.com	3046788329	Calle 9A #27-82	Galapa - Atlántico 	01        	2025-06-25 05:36:24.091787
\.


--
-- Name: contactos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('contactos_id_seq', 1, true);


--
-- Data for Name: cursos; Type: TABLE DATA; Schema: public; Owner: emmaus
--

COPY cursos (id, nombre, nivel_id, descripcion, id_material, id_tipo, valor_costo, valor_venta, consecutivo, created_at, updated_at, deleted_at) FROM stdin;
43	La juventud y los planes de Dios	3	La Juventud y los Planes de Dios es un curso bíblico en cuatro lecciones que procura enseñar al joven cristiano cómo encontrar y seguir la voluntad de Dios.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
11	San Pedro y la Iglesia	2	Este curso está escrito para nuevos creyentes en el Señor Jesucristo. Su propósito es ayudar creyentes nuevos crecer en la vida cristiana. Este curso bíblico ayuda contestar preguntas como: ¿Cómo puedo estar seguro de mi salvación? ¿Dónde debo asistir a la iglesia? ¿Dios tiene un plan para mi vida? Mira la lista de lecciones abajo para ver la gama de temas abarcadas.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
41	La juventud encuentra a Dios 	3	La juventud encuentra a Dios, contiene cuatro lecciones para jovenes, deseosos de tener una fe racional, verdadera y sincera frente a Dios.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
1	El Siervo de Dios	1	Lo que necesita este mundo es un Siervo y Marcos nos presenta a Jesucristo como el Siervo perfecto. Este curso cubre el evangelio de Marcos en doce lecciones sencillas. El evangelio de Marcos se concentra en las cosas que hizo Jesús y adonde iba. Es el más corto de los evangelios, y probablemente fue el primero en escribirse. Marcos presenta súbitamente la acción y en el orden aproximado en que ocurrieron los eventos. Muchas veces, Marcos explica las costumbres judías para que sus lectores puedan entenderle mejor. Su meta es presentarnos al Siervo perfecto, el Hijo de Dios, quien es el Señor Jesucristo.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
2	El Verbo de Dios	1	En su evangelio del Señor Jesucristo, Juan enfatiza el hecho de que Jesús vino a salvarnos de nuestro pecado. Este curso cubre el evangelio de Juan en doce lecciones sencillas. El apóstol Juan era un hombre mayor cuando escribió su evangelio. Fue escrito años después de los otros tres evangelios de Jesucristo que habían sido puestos en circulación. La Iglesia ya estaba siendo atormentada por el error y por ataques contra la persona y la obra del bendito Hijo de Dios. Entonces, Juan escribió este evangelio para decir al hombre, que éste es Jesús, “He aquí, tu Dios”. Juan se dedica a presentar los milagros y las palabras de Jesús y luego interpretarlas para sus lectores. Juan selecciona su material para que “creáis que Jesús es el Cristo, el Hijo de Dios, y para que creyendo, tengáis vida en su nombre” (Juan 20:31).	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
3	El Salvador del mundo	1	Lucas le preguntó a las personas que conocieron a Jesús que le contasen acerca de lo que habían visto y oído de Él, entonces él recopiló todas esas verdaderas acerca de Jesús y el Espíritu Santo lo ayudó para que no cometiese ninguna equivocación acerca de la verdad. En su evangelio, él nos habla acerca de Juan el Bautista, de María la madre de Jesús y de José, el marido de María. También aprendemos que los amigos de Jesús le dieron la bienvenida en sus casas; que muchas personas odiaron a Jesús y tomaron la decisión de matarle; que Jesús sanó y ayudó a muchos, aprendemos de todo lo que enseñó a la gente, también aprendemos acerca de los ángeles, de Pilato el Gobernador de Roma, etc. ¡Qué historia! Este curso te ayudará a estudiar el evangelio de Lucas y tener un mejor entendimiento de éste. Nuestro deseo es que puedas entender y compartir con otros cómo Jesús es el Salvador del mundo, y ha provisto perdón de pecados para todos aquellos que se arrepienten (Lucas 24:47).	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
4	El hombre más grande	1	¿Quién es la persona más grande que ha caminado sobre esta tierra? Su nombre es Jesucristo. Tanto si conoces mucho de Él como si conoces poco, este curso te ayudará a descubrir más de Él para tu beneficio. Aprenderás en la Biblia, quién fue realmente Jesús, lo que hizo y cómo puedes conocerle personalmente.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
5	La Biblia dice así 	1	Este curso tiene muchísimas preguntas . . . y sus respuestas. Todas son de la Biblia. Este curso es distinto a los otros en formato. Cada lección empieza con un versículo de la Biblia y luego hace preguntas y da respuestas en cuanto al versículo y la información que proporciona. Por medio de este formato basado en preguntas y respuestas, el estudiante aprende acerca de la Biblia, Dios, Jesucristo, el pecado y cómo ser salvo. El formato es útil para ir memorizando los versículos usados en cada lección.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
6	Un encuentro con Cristo	1	La Biblia es una historia acerca de Dios entrando en la historia humana para salvar a sus criaturas rebeldes a través de la persona y obra de Jesucristo. La gran extensión de la Biblia hace que sea difícil hacer un seguimiento de lo que dice sobre los temas que son fundamentales para entender dicha historia.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
7	Lo que la Biblia enseña	1	En este curso, aprenderás lo que la Biblia enseña acerca de los asuntos esenciales de Dios, la salvación y la fe.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
8	Un Dios, Un camino	1	Hay un solo Dios y debiéramos desear conocerle. Nuestro futuro depende de eso ya que Dios se ha mostrado a nosotros “tal como El es”. Este curso nos ayudará a entender a Dios y como llegar a Él. Necesitamos ir a El, tenemos que atravesar el camino que Él ha diseñado, y este curso le guiará en ese camino.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
9	Llaves de oro	1	Las llaves se hacen de todas clases en cuanto a formas y medidas. En este curso de estudio descubrirás doce “llaves” que abrirán “puertas” a algunas de las verdades más importantes que jamás aprenderás. En este breve curso aprenderás a usar estas importantes llaves para abrir puertas donde encontrarás verdades acerca de Dios, la salvación, la muerte y la vida cristiana. Tómalas una por una, y entra tú mismo a la casa del tesoro de la verdad de Dios, la Biblia.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
10	Puedes vivir para siempre	1	Este curso usa un método de estudio didáctico por preguntar y contestar preguntas sobre cada tema. Comprenderás las respuestas de algunas de las preguntas más importantes y difíciles de la vida, tales como: ¿Cómo es Dios? ¿Es la Biblia la Palabra de Dios? ¿Cuál es el destino del hombre? ¿Puede el hombre tener certeza que hay vida después de la muerte? ¡Ven y descubre las repuestas en la Biblia!	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
12	Lecciones para la vida cristiana	2	Conocer y seguir a Jesucristo cambia dramáticamente cada aspecto de la vida de un cristiano y causa muchas preguntas como, ¿Cómo debo orar? ¿Qué es el bautismo? ¿A qué iglesia debo ir? ¿Cómo puedo conocer a Dios y acercarme más a Él? Este curso ha sido escrito en un claro y sencillo formato para responder esas preguntas y muchas otras más. Pero aún más importante, este curso intenta equiparte para crecer en tu relación con Dios al ayudarte a estudiar su Palabra. A medida que estudies las 12 lecciones en este curso, empezarás a construir una base sólida para vivir una vida nueva en Cristo.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
13	Nacido para triunfar	2	Este curso está diseñado para animarte a llegar a ese punto en tu vida cuando, a través de una relación salvadora con Jesucristo, puedes empezar a vivir para la gloria de Dios. Nadie debe pensar que ha nacido para perder. Hemos nacido para triunfar. ¿Por dónde podemos empezar? Alguien lo expresó de esta manera: “Hay una senda para regresar de los caminos oscuros del pecado a Dios; Hay una puerta abierta por la cual puedes entrar. Tienes que empezar por la cruz del Calvario y venir como pecador a Jesucristo”.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
14	Prisionero con Jesús	2	Los nuevos creyentes enfrentan problemas reales al procurar vivir la vida cristiana; todavía más si el nuevo creyente se encuentra en una prisión. Este curso está diseñado para ayudar a los creyentes a superar los problemas reales de la vida. Asuntos como la culpa y el perdón, el deseo sexual, el matrimonio y el divorcio y la sujeción a la autoridad son tratados de una manera clara y veraz. Sigue adelante y lee el curso; comprobarás cómo puedes servir al Señor con tu manera de vivir.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
15	Como vivir en libertad	2	Todos nosotros queremos tener éxito en la vida. Esto es especialmente cierto para los presos cristianos cuando son liberados de la prisión. Uno de los problemas a los que se enfrentan es el tomar decisiones cotidianas: algo que no tenían que hacer cuando estaban tras las rejas. Este estudio te ayudará a identificar las áreas de tu vida donde se necesita la obediencia para que puedas tener éxito en la vida. Cuando salen de prisión tienen muchas decisiones que tomar diariamente. A menudo esta faceta de la libertad los abruma y vuelven al crimen y, finalmente, a la cárcel. Se estima que tres de cada cuatro personas que salen de prisión vuelven a ella. La clave del éxito en la vida del que está en libertad condicional es la misma que para cada uno de nosotros: completa obediencia a la voluntad revelada de Dios.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
16	Guia para el crecimiento cristiano	2	¡He creído! Y ahora, ¿qué hago? Abarcando temas como la adoración, la memorización de la escrituras, y las buenas obras, este curso ayudará al nuevo creyente crecer en su fe. Tu vida nueva en Cristo debe cambiar tu manera de pensar, hablar y vivir. Este curso te ayudará a ver cómo el hecho de ser cristiano tiene un efecto sobre toda decisión que hagas en tu vida.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
17	Acuérdate de tu creador	2	Génesis es el libro de comienzos; en Génesis leemos acerca de la creación del mundo, la caída del hombre en el pecado, la promesa de un Salvador y el esparcimiento del hombre sobre la faz de la tierra. En Génesis escuchamos por primera vez acerca de Abraham y seguimos sus pisadas desde que vivía en Canaán hasta que se fue a Egipto. Entender el libro de Génesis nos da una buena base para entender el resto de la Biblia y el plan de Dios para salvarnos de la condenación del pecado.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
18	12 Lecciones para nuevos creyentes	2	Todo ser humano necesita comida y agua y aire puro. Nadie puede vivir ni crecer sin estas cosas. Los nuevos creyentes han nacido de nuevo. Tienen vida pero necesitan la comida y bebida, la verdad de Dios. Necesitan la Biblia. Este libro 12 lecciones para nuevos creyentes por el Sr. C.E. Tatham, señala algunas de las verdades que el nuevo creyente debe saber. El Sr. Tatham ha estado alimentando a los hijos de Dios durante muchos años. Ve los 12 títulos para ver cuán útil puede ser este libro.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
40	La mujer que agrada a Dios	3	Más relevante hoy que nunca, vemos lo que dice la Biblia sobre el papel de la mujer en el hogar, la iglesia y el mundo de hoy. La primera responsabilidad de todos los cristianos, hombres y mujeres, es encontrar el lugar que Dios ha fijado para ellos. No hay honor mayor que el de ser lo que Dios quiere que seamos y hacer lo que Él nos manda hacer. ¿Cuál es la voluntad de Dios para las mujeres? El propósito de este curso es examinar lo que la Biblia dice sobre las mujeres para que podamos: Determinar el plan y el propósito de Dios para las mujeres; Determinar qué cualidades de carácter agradan a Dios; Descubrir los principios del Nuevo Testamento sobre la conducta y las relaciones de la mujer; Descubrir qué ministerios hay abiertos a las mujeres en el hogar, la iglesia, el mundo secular y el campo misionero.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
19	El rey venidero	2	Lo que este mundo necesita es un Rey; uno que gobierne con firmeza, sabiduría y compasión. Este es el tipo de Rey que Dios ha planeado para este mundo. Se llama Jesucristo, y es el Hijo de Dios. Cuando el Hijo de Dios vino a la tierra por primera vez, fue rechazado por el pueblo en el que nació, los judíos. Mateo cuenta cómo le dieron una cruz, no una corona real. Sin embargo, Jesús gobierna hoy sobre un reino. Su reinado actual es espiritual sobre las mentes, los corazones y las almas de las personas que se han rendido a Él reconociéndolo como el Rey de sus vidas. Él prometió a Sus seguidores que un día volvería, y cuando vuelva, las gloriosas predicciones de los profetas en la Biblia se cumplirán, y Jesús reinará no sólo sobre la nación judía sino también en el mundo entero. Mateo conocía bien a Jesús. Su relato acerca de la vida terrenal de Jesús nunca pierde de vista el hecho de que Jesús es el Rey de los judíos (es decir, el heredero legítimo del trono del rey David) y de este mundo tan necesitado de un Soberano. Conforme vayas estudiando el evangelio según Mateo con la ayuda de este curso, EL REY VENIDERO, se desarrollará la historia. Se percibe claramente que Mateo amaba al Rey Jesús y deseaba que llegara el día cuando Él reinara. Nuestra esperanza es que esta pasión y perspectiva gane también tu corazón.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
20	¿Debo ser bautizado?	2	Muchas congregaciones Cristianas dictan algunas clases para preparar al nuevo Creyente para el paso del bautismo. En el curso “¿Debo ser Bautizado?” se contestan una variedad de inquietudes que tiene el nuevo creyente, tales como: ¿Necesito el bautismo para recibir el perdón? ¿Qué compromisos adquiero cuando me bautizo? ¿Cómo se debe practicar el bautismo Cristiano? Más que suministrar información Bíblica sobre el tema, el curso motiva al estudiante a preguntarse “¿qué impide que yo sea bautizado?” y luego a buscar activamente la oportunidad de tomar ese paso de obediencia.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
21	En memoria de mi 	2	Jesucristo dijo: “Haced esto en memoria de Mí”. ¿Qué significa? ¿Quién puede o debe participar? ¿Cómo se practica? Este curso nos llevará a la Biblia para las respuestas a estas preguntas. La Cena del Señor. ¡Sólo pan y sólo vino! Con estas cosas tan simples y comunes, el Señor Jesús nos pidió hacer memoria de Él. Pan partido primero por manos que pronto habían de ser traspasados. Vino vertido por Él. Dado a Sus discípulos para que recordasen Su muerte y Su segunda venida. ¿Qué conocemos en cuanto a este acto de hacer memoria? Este curso llevará al estudiante a las Escrituras para descubrir el propósito y la práctica de la Cena del Señor.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
33	Seguridad Eterna	3	La Biblia enseña que Dios dio a Su Hijo para que todos los hombres puedan tener la vida eterna. Es un don de Dios, no el pago por buenas obras. Hay muchos versículos que apoyan la doctrina de la seguridad eterna. ¿Enseña esto que el cristiano pueda hacer como le plazca? Claro que no. La verdad de la seguridad eterna debe ser balanceada por otra verdad, igualmente bíblica, que Dios seguramente castigará a Su hijo errante. Un Padre santo nunca dejará que Su hijo siga deshonrando Su nombre. Esta disciplina es para enseñarnos aquí en la tierra a no pecar contra Dios.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
22	Las Epístolas de Juan 	2	En las tres cartas de Juan, las responsabilidades y los privilegios del creyente en la familia celestial son presentados intransigentemente. Las cartas de Juan son prácticas. ¡También pueden ser perturbadoras! Juan no deja zonas grises en nuestras vidas. Con él, las cosas son o blancas o negras, correctas o incorrectas, verdaderas o falsas, buenas o malas. Dios es luz, y no hay ningunas tinieblas en Él. Por lo tanto, estas cartas son especialmente aplicables a nuestra época. Estamos viviendo en días cuando, por lo que al mundo se refiere, la filosofía prevalente es que la moralidad depende de la situación y la religión es privada y no tiene porqué afectar nuestra conducta. Las escrituras de Juan quitan estas confusas telas de arañas de las mentes del pueblo de Dios.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
23	¿Primero Yo?	2	Estudios sobre el Discipulado Usted no puede aceptar a Jesucristo como Salvador a menos que también lo acepte como Señor. Sólo hay un Jesucristo-Él es tanto Señor como Salvador. ¿Yo Primero? debe ser CRISTO PRIMERO!	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
24	Tu palabra es verdad	2	Uno no puede creer en la Biblia, ¿no? El fundamento de la fe cristiana es el gran hecho de la inspiración verbal y la fiabilidad de la Biblia. Esta enseñanza importante es defendida en este curso. El hecho de la inspiración divina de la Palabra de Dios es el fundamento de toda la enseñanza de sus hojas sagradas. Como resultado, esto ha sido severamente atacado en los últimos 200 años por el racionalismo, el modernismo y otros tipos de escepticismo. Hoy el asalto continúa sin disminuirse, y se encuentra en lugares y círculos que llevan mucho tiempo siendo conocidos por su adherencia a la inspiración verbal de la inerrante Palabra de Dios. En este curso, hemos provisto varias evidencias para apoyar la inspiración y la fiabilidad de la Biblia	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
25	Santiago	2	“La fe a prueba” es el tema de esta carta. En cinco cortos capítulos, Santiago pone nuestra fe a prueba. Él quiere saber si en verdad es genuina o una imitación barata. Con lenguaje sencillo y usando frases cortas, sentimos el impacto de las palabras de Santiago. El propósito de la carta no es tanto el enseñar doctrina como ayudarnos a aplicar la doctrina a nuestras vidas, y mostrarnos cómo deberíamos manifestar la vida del Cristo resucitado ante los que nos rodean. Este estudio te retará a revisar tu vida y detectar si estás viviendo tu fe en el Señor Jesucristo.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
26	Bases para un hogar cristiano	2	Este curso ha sido preparado para ayudarte a entender cabalmente algunas de las verdades contenidas en la Palabra de Dios, en torno al tema propuesto, esto es, el hogar cristiano. No solo te impartirá algunos conocimientos doctrinales, sino que también te ayudará en la vida práctica, y también contribuirá a tu crecimiento espiritual. Por lo tanto, dedica diariamente unos momentos para estar a solas en comunión con Dios.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
27	Los dos serán uno 	2	¿Es posible lograr la verdadera felicidad en el matrimonio? ¿Cómo tener un matrimonio bendecido por Dios? ¿Por qué será que hay fracasos e infelicidad aún en las parejas cristianas? Estas y otras preguntas son contestadas de una manera clara y bíblica en las lecciones de este sencillo pero importante curso sobre el matrimonio. Su autor, Ricardo Khol, afirma que “el matrimonio es mucho más que una relación física o social entre un hombre y una mujer. Desde el principio, fue diseñado para ser una experien-cia profundamente espiritual”.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
28	Un diseño para tu matrimonio 	2	La vida familiar es importante en cada generación, por lo tanto, este práctico curso proveerá muy buenos consejos en cuanto a los fundamentos bíblicos del matrimonio y la familia; incluyendo temas como los roles en el hogar, tanto del esposo como de la esposa y los roles de los padre para la crianza de sus hijos. También proveerá consejos para mantener un ambiente espiritual en el hogar y ayudará a establecer metas en relación a esto. Este estudio ayudará a parejas que están pensando casarse y también a los que ya llevan tiempo casados para que comprendan mejor el diseño de Dios para su matrimonio.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
29	Mas creced en la Gracia 	2	Mas Creced en la Gracia Vivir la vida cristiana victoriosamente. . . la meta de todos los creyentes en el Señor Jesucristo. Este curso llevará al estudiante a la esencia del cristianismo bíblico y le enseñará cómo la verdad de la escritura nos libra para vivir victoriosamente en Cristo. El éxito personal–¡esa es la promesa de Dios! “Y conoceréis la verdad, y la verdad os hará libres.” (Juan 8:32). Esta libertad es simplemente el éxito personal para el cristiano. Es la libertad, la habilidad y el derecho de hacerte de la clase de persona que Dios tenía pensado cuando te creó. En vez de estar esclavizados por nuestro pecado, paralizados por nuestra ignorancia y atados por nuestras tensiones-Dios ha prometido la libertad. Este curso retará al estudiante a pensar en el cristianismo bíblico, sus características y su aplicación real en nuestras vidas. ¿Cómo, pues, debemos vivir? Este curso te enseñará hacerlo.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
30	Sumario de la Biblia 	2	¿Quieres tener un sumario de la biblia? ¿Tener un mejor entendimiento de lo que contiene el Antiguo Testamento tanto como en el Nuevo? Pues, ¡este curso es para tí! Este curso es para dos clases de personas. Primeramente, es para los que conocen o muy poco o nada sobre la Biblia pero que quieren familiarizarse con este “Libro de todos libros”. Mucha gente quiere estudiar la Biblia pero se desanima por el gran tamaño del Libro y por su ignorancia de qué se trata. Cree que si tuviera un sumario general de la historia, sería capaz de leer y entenderla.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
31	Cristo amó a la Iglesia 	2	Cuando los hombres piensan en la Iglesia, suelen pensar en un vasto sistema organizado de la religión, pero, ¿qué quiere decir “la Iglesia” en el Nuevo Testamento? ¡Este curso nos da la respuesta! Cuando los hombres piensan en la Iglesia, suelen pensar en un vasto sistema organizado de la religión que empezó a evolucionarse un siglo después de la edad apostólica, que bajo Constantino llegó a ser la religión del estado del Imperio Romano. Hoy varios grupos religiosos organizados en todo el mundo (el Católico Romano, el Ortodoxo Griego, etc.) lo representan. Pero, ¿es eso lo que quiere decir “la Iglesia” en el Nuevo Testamento? ¿Cuál es el parecer de Dios sobre la Iglesia? ¿Cómo fue la Iglesia originalmente? ¿Cómo debe ser hoy en día? ¡Qué hable la Biblia sobre el asunto! Para algunos, los pensamientos expresados en este curso serán nuevos, quizás incluso revolucionarios. Otros reconocerán un modelo que bien conocen. Pero dejemos que los estudiantes midan su entendimiento de la Iglesia contra lo que se nos demuestra en la Biblia.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
32	La Biblia ¿qué contiene para ti?	3	La Biblia. Muchas personas hablan acerca de este libro, el más publicado, pero ¿de qué trata? Para ser más específico, ¿qué contiene para ti? Este curso te presentará la Biblia—cómo fue compuesta, su contenido, sus principales caracteres y eventos—con la esperanza de que tú la quieras estudiar, llegues a conocer a su autor final y descubras el importante mensaje que tiene para ti personalmente.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
34	Romanos	3	En esta carta importante, Pablo explica claramente la perdición del hombre. Entonces, el llega al tema central de la epístola, que es el plan de Dios de salvación. Habiendo sido salvo, Pablo explica como el hombre puede llegar a ser libre del poder del pecado y vivir en victoria en Cristo. El toma tres capítulos para explicar el plan de Dios para su pueblo Israel, mostrando que ellos vendrán a entender la gracia de Dios también. El termina su epístola tratando muchos temas prácticos, los cuales al ser practicados dan evidencia de que uno es identificado con Cristo.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
35	Sepultados con Cristo	3	Bautismo, ¿Qué es? ¿Qué significa? ¿Cuándo se practica? ¿Cómo se hace? Cubriendo toda referencia nuevotestamentaria sobre el bautismo, da las respuestas a estas preguntas y más. ¡Bautismo! Pocos temas han causado tanta inquietud y controversia, dentro y fuera de la Iglesia. Hay muchas opiniones en cuanto al bautismo, y son fuertemente mantenidas. En este curso se estudia cada pasaje del Nuevo Testamento que tiene que ver con el bautismo. Se escudriñan en su contexto y a la luz de otras escrituras que tratan el tema. Se repasan los distintos bautismos en las escrituras. Se examina la naturaleza del bautismo encomendado por Cristo (Mateo 28:19-20). Se explica la relación que tiene el bautismo con la salvación, y se trata el bautismo en el Espíritu Santo.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
36	¿Puedes conocer a Dios?	3	Este es el libro que usted ha estado buscando. En estas páginas encontrará explicaciones, en lenguaje sencillo, de las doctrinas básicas de la fe cristiana que serán convincentes para el que no cree en Dios y de gran ayuda para el que empieza a creer.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
37	Doctrinas Bíblicas básicas 	3	¡La Biblia es un libro maravilloso! Esta compuesta por sesenta y seis secciones relacionadas entre si, a las que llamamos “libros”, pero entre todas esas secciones conforman un solo libro, consistente y unificado en su mensaje. Podemos estudiar la Biblia de varias maneras: libro por libro, en devocionales diarios, biográficamente, por temas etc. El propósito de este curso es guiar al alumno al estudio de las doctrinas principales de las Escrituras. Una ventaja importante de este método sistemático de estudio bíblico es que uno puede estudiar todo lo que la Biblia enseña desde Génesis a Apocalipsis sobre cualquier tema. “Enséñame, Jehová, tu camino, y caminaré yo en tu verdad; afirma mi corazón para que tema tu nombre” (Salmos 86:11). Cada una de las doctrinas principales de las Escrituras, estarán apoyadas por los versículos claves y pasajes relacionados a cada tema.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
38	Hechos	3	Este estudio emocionante en el libro de los Hechos nos introduce a la expansión del Evangelio desde Jerusalén a Judea y Samaria y hasta los fines del mundo conocido. El libro de los Hechos es la segunda parte del relato de Lucas sobre la historia de Cristo y la iglesia primitiva. El libro nos relata cómo la iglesia primitiva hizo frente al reto de la Gran Comisión. Cubre una etapa de unos 30 años desde el nacimiento de la iglesia en el día de Pentecostés hasta el final del encarcelamiento de Pablo en Roma. Es una historia de personas, lugares y principios. Relata la extensión del cristianismo, como empezó en Jerusalén y atravesó lo que hoy en día es Siria, Turquía y Grecia, hasta que por fín llegó a Roma. Empieza con Pedro, nos presenta a Felipe, Esteban y Bernabé y termina con Pablo. Nos provee principios para las misiones, el servicio y el tratamiento de problemas en la iglesia. Lucas nos proporciona una historia emocionante de la iglesia primitiva.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
39	Da cuenta de tu Mayordomía 	3	En este libro aprenderemos: 1. Que somos mayordomos, no dueños de todo lo que poseemos. 2. Que un día tendremos que rendir cuentas del uso que hemos dado a todo lo que ha pasado por nuestras manos. 3. Algunos principios bíblicos de economía. Confiamos que la aplicación de estos principios a nuestras finanzas, nos permita acercarnos a Dios con confianza cuando escuchemos “Da cuenta de tu Mayordomía”.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
42	La juventud enfrenta la vida 	3	La Juventud Enfrenta a la Vida es un curso bíblico en cuatro lecciones que trata sobre los principales problemas que encara el joven cristiano que procura vivir para Dios.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
44	1 Corintios	3	La primera carta de Pablo a los creyentes en Corinto se ocupa de unas cuestiones importantes. ¿Van de acuerdo con las Escrituras las denominaciones? ¿Por qué pecados puede una persona ser excomulgada de la iglesia? ¿Está bien que un cristiano lleve a otro creyente ante los tribunales? ¿Qué debemos pensar sobre el matrimonio y el divorcio? ¿Cuál es el papel de la mujer en la iglesia? ¿Qué es el don de lenguas? ¿Cómo será el cuerpo resucitado? Este estudio te ayudará a saber las respuestas a estas preguntas. Esta carta fue escrita originalmente para contestar ciertas preguntas que habían surgido en la asamblea en Corinto. Pero muchos de estos problemas están todavía con nosotros y lo estarán hasta el final de esta época. Entonces, la primera carta de Pablo a los Corintios tiene un mensaje para nosotros hoy, un mensaje actual.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
45	El otro consolador 	3	El propósito de este curso es presentar con sencillez, pero con apego a la Biblia, la verdad acerca del OTRO CONSOLADOR que el Padre ha enviado para que esté con nosotros para siempre (Juan 14:16). La Palabra de Dios indica el camino del cristiano. El Espíritu de Dios provee el poder necesarios para andar por este camino.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
46	Antiguo Testamento: Ley e historia	3	El Antiguo Testamento se compone de treinta y nueve libros. Se divide en las secciones de Ley (Génesis-Deuteronomio), Historia (Josué-Ester), Poesía (Job-Cantares) y Profecía (Isaías-Malaquías). Este libro está dedicado al estudio general de las secciones de Ley e Historia de la Biblia. Abarca desde el libro de Génesis hasta el libro de Ester. Obtendrás una visión general del libro de los comienzos, del deambular de Israel por el desierto, su entrada en la tierra de Canaán y del periodo de los Jueces y los Reyes. El autor ha aportado muchas y lúcidas apreciaciones que permitirán al estudiante apreciar estas ricas porciones de la Palabra de Dios.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
47	Antiguo Testamento: Poesía y profecía 	3	Este curso se dedica al estudio general de las secciones de Poesía y Profecía de la Biblia. Hay cinco libros que son específicamente etiquetados como libros de poesía, a saber: Job, Salmos, Proverbios, Eclesiastés y Cantar de los Cantares. Estos libros nos ayudan a abordar muchas de las circunstancias vitales que encaramos cada día, tales como el sufrimiento, la soledad, la ira, la pena y el estrés. Los profetas fueron portavoces o voceros que representaban a Dios ante los hombres. Su labor consistía en llamar a los judíos al arrepentimiento del pecado y en guiarlos a la obediencia de la ley de Dios. Cada libro de profecía es tratado y situado en su contexto histórico. El autor ha aportado muchas y lúcidas apreciaciones que permitirán al estudiante valorar estas ricas porciones de la Palabra de Dios.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
48	Panorama general del Nuevo Testamento	3	La Biblia es un gran libro que cubre muchos siglos de historia, culturas y personas. A pesar de su tamaño y diversidad, los escritores del Nuevo Testamento afirman que Jesucristo es el centro y punto culminante de toda la Escritura (Lc. 24: 25-27). Cada uno de los 27 libros del Nuevo Testamento respalda esta afirmación a su manera. Están escritos para diferentes audiencias en contextos únicos y por diferentes autores en diversas situaciones, pero todos ellos colocan a Jesucristo en el corazón de la historia del mundo como el Salvador que trae salvación y perdón a todos los que lo siguen.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
49	La Evangelización Personal 	3	Este curso está diseñado para ayudar a equiparte por ti mismo para el mayor\nde todos los ministerios, el de llevar hombres y mujeres, jóvenes o mayores\na Cristo. Con el propósito de ayudarte a comprender la tarea se dan muchas\nreferencias a las Escrituras. Asegúrate de mirarlas y leer el texto bíblico. Si\ntienes interés en llegar a ser un ganador de almas, querrás memorizar estos y\notros versículos. Otra buena idea sería subrayar estos versículos en tu Biblia.\nEncontrarás decenas de versículos en el curso que son dignos de ser añadidos\na tu lista de versículos a memorizar. Mientras más Escrituras puedas encontrar\nfácilmente en tu Biblia, más confianza tendrás para hablar a las personas sobre\nsus necesidades espirituales.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
50	Tesalonicenses	3	Las cartas de Pablo a los creyentes en Tesalónica fueron probablemente algunas de las primeras escrituras inspiradas que manaban de su pluma. La primera carta contesta las preguntas que surgieron en la iglesia después de su salida. Proporciona instrucción sobre el comportamiento sexual, la conducta cristiana y el regreso del Señor. La segunda carta de Pablo llegó unos meses después, y reafirmó las enseñanzas de la primera carta, y aclaró uno asuntos acerca del regreso del Señor.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
51	Enseñando en Escuela dominical 	3	Las sugerencias contenidas en este libro se presentan para ayudar a los creyentes que, con vocación y dedicación, desean un mayor éxito en la enseñanza de la Palabra de Dios. Este material no se ofrece al lector como manual Infalible o un texto absoluto para la instrucción de la Escuela Dominical, más bien se ha preparado como guía elemental del orientación para los que quieren servir al Señor en este noble ministerio.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
52	El hombre perfecto	3	Lucas, el doctor, escribió el tercer Evangelio como el Espíritu Santo lo dirigió. Este Evangelio muestra a nuestro Señor Jesús cristo como el Hombre Perfecto. También fue Hijo de Dios, y esto lo muestra Lucas con claridad. El Hombre Perfecto, el Hombre Jesucristo, nos mostró todas las glorias de Dios. Este libro le ayudará a conocer mejor al Señor Jesús para así conocer mejor a Dios.	\N	\N	\N	\N	\N	2025-10-08 14:11:41.581863-05	2025-10-08 14:11:41.581863-05	\N
\.


--
-- Name: cursos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('cursos_id_seq', 52, true);


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
1	1	22474166	010	Marlenys		Fragoso	De Leon	3126139012	marlenysfragoso@gmail.com	Barranquilla	Santuario	2025-06-25 12:50:32.58138	Casado	Secundaria	Independiente
2	1	22615758	011	Madeleine	Yasira	Sepulveda	Garcia	3128593830	sepulvedamadeleine@gmail.com	Barranquilla	Santuario	2025-06-25 12:54:22.477342	Soltero	Secundaria	Ama de Casa
3	1	1044213424	012	Brendy		Muñoz	Acuña	3160965507	brandymu0515@gmail.com	Barranquilla	Santuario	2025-06-25 13:05:33.416159	Soltero	Universitario	Estudiante
4	1	1139428914	013	Kevin		Munive	Castro	3001530804	kevinmunivecastro@gmail.com	Galapa	Santuario	2025-06-25 13:08:08.845426	Soltero	Universitario	Estudiante
5	1	1044216775	014	Briyit		Muñoz	Acuña	3160965507	briyitmu0@gmail.com	Barranquilla	Santuario	2025-06-26 16:16:10.750269	Soltero	Universitario	Estudiante
6	1	1043670635	015	Jesus	David	Ahumada	De Avila	3216103948	jesusdavid132006@gmail.com	Barranquilla	Santuario	2025-06-26 16:20:50.227171	Soltero	Universitario	Estudiante
\.


--
-- Data for Name: estudiantes_cursos; Type: TABLE DATA; Schema: public; Owner: emmaus
--

COPY estudiantes_cursos (id, estudiante_id, curso_id, fecha, porcentaje, enviado) FROM stdin;
1	4	1	2025-06-25	96	f
2	4	2	2025-06-25	98	f
3	4	6	2025-06-26	92	f
4	4	9	2025-06-26	96	f
5	3	2	2025-06-26	96	f
6	4	5	2025-06-26	100	f
7	3	5	2025-06-26	98	f
8	1	1	2025-06-26	93	f
9	1	2	2025-06-26	93	f
10	1	5	2025-06-26	97	f
11	1	6	2025-06-26	89	f
12	1	8	2025-06-26	93	f
13	1	7	2025-06-26	85	f
14	1	19	2025-06-26	92	f
15	1	3	2025-06-26	93	f
16	1	15	2025-06-26	88	f
17	1	4	2025-06-26	99	f
18	1	13	2025-06-26	93	f
19	1	35	2025-06-26	73	f
20	1	11	2025-06-26	84	f
21	1	21	2025-06-26	83	f
22	5	1	2025-06-26	89	f
23	6	1	2025-06-26	86	f
24	2	1	2025-06-26	86	f
25	2	2	2025-06-26	89	f
26	2	5	2025-06-26	97	f
27	2	6	2025-06-26	87	f
28	2	8	2025-06-26	93	f
29	2	11	2025-06-26	86	f
30	2	32	2025-06-26	91	f
31	2	4	2025-06-26	95	f
32	2	17	2025-06-26	91	f
33	2	3	2025-06-26	90	f
34	2	21	2025-06-26	77	f
35	1	12	2025-08-30	88	f
36	1	20	2025-08-30	90	f
37	1	17	2025-08-30	93	f
38	1	14	2025-08-30	87	f
39	1	10	2025-08-30	93	f
40	2	16	2025-08-30	86	f
41	2	31	2025-08-30	72	f
42	2	19	2025-08-30	92	f
43	2	13	2025-08-30	91	f
44	2	12	2025-08-30	87	f
45	2	18	2025-08-30	88	f
46	2	14	2025-08-30	74	f
\.


--
-- Name: estudiantes_cursos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('estudiantes_cursos_id_seq', 46, true);


--
-- Name: estudiantes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('estudiantes_id_seq', 6, true);


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
1	PRIMER NIVEL
2	SEGUNDO NIVEL
3	TERCER NIVEL
4	CUARTO NIVEL
5	QUINTO NIVEL
\.


--
-- Name: niveles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('niveles_id_seq', 5, true);


--
-- Data for Name: niveles_programas; Type: TABLE DATA; Schema: public; Owner: emmaus
--

COPY niveles_programas (id, programa_id, nombre, version) FROM stdin;
1	1	PRIMER NIVEL	1
2	1	SEGUNDO NIVEL	1
3	1	TERCER NIVEL	1
\.


--
-- Name: niveles_programas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('niveles_programas_id_seq', 3, true);


--
-- Data for Name: observaciones_estudiantes; Type: TABLE DATA; Schema: public; Owner: emmaus
--

COPY observaciones_estudiantes (id, estudiante_id, observacion, fecha, usuario_id, tipo) FROM stdin;
1	1	Se realiza devolución del curso Lo que la Biblia Enseña	2025-06-26 15:55:26.469371	6	General
\.


--
-- Name: observaciones_estudiantes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('observaciones_estudiantes_id_seq', 1, true);


--
-- Data for Name: programas; Type: TABLE DATA; Schema: public; Owner: emmaus
--

COPY programas (id, nombre, descripcion, current_version, created_at, updated_at) FROM stdin;
1	Teología	Programa teológico sistemático con énfasis doctrinal y práctico, que lleva al estudiante en un recorrido libro por libro de la Biblia y lo capacita en las áreas de estudio e interpretación, vida cristiana, familia, Evangelismo, discipulado y consejería, y formación ministerial.	1	2025-10-22 21:20:00.107515	2025-10-22 21:20:00.107515
\.


--
-- Data for Name: programas_asignaciones; Type: TABLE DATA; Schema: public; Owner: emmaus
--

COPY programas_asignaciones (id, programa_id, estudiante_id, contacto_id, fecha_asignacion, version) FROM stdin;
\.


--
-- Name: programas_asignaciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('programas_asignaciones_id_seq', 1, false);


--
-- Data for Name: programas_cursos; Type: TABLE DATA; Schema: public; Owner: emmaus
--

COPY programas_cursos (id, programa_id, curso_id, nivel_id, consecutivo, version) FROM stdin;
1	1	1	1	1	1
2	1	2	1	2	1
3	1	3	1	3	1
4	1	4	1	4	1
5	1	5	1	5	1
6	1	6	1	6	1
7	1	7	1	7	1
8	1	8	1	8	1
9	1	9	1	9	1
10	1	10	1	10	1
11	1	11	2	11	1
12	1	12	2	12	1
13	1	13	2	13	1
14	1	14	2	14	1
15	1	15	2	15	1
16	1	16	2	16	1
17	1	17	2	17	1
18	1	18	2	18	1
19	1	19	2	19	1
20	1	20	2	20	1
21	1	21	2	21	1
22	1	22	2	22	1
23	1	23	2	23	1
24	1	24	2	24	1
25	1	25	2	25	1
26	1	26	2	26	1
27	1	27	2	27	1
28	1	28	2	28	1
29	1	29	2	29	1
30	1	30	2	30	1
31	1	31	2	31	1
32	1	32	3	32	1
33	1	33	3	33	1
34	1	34	3	34	1
35	1	35	3	35	1
36	1	36	3	36	1
37	1	37	3	37	1
38	1	38	3	38	1
39	1	39	3	39	1
40	1	40	3	40	1
41	1	41	3	41	1
42	1	42	3	42	1
43	1	43	3	43	1
44	1	44	3	44	1
45	1	45	3	45	1
46	1	46	3	46	1
47	1	47	3	47	1
48	1	48	3	48	1
49	1	49	3	49	1
50	1	50	3	50	1
51	1	51	3	51	1
52	1	52	3	52	1
\.


--
-- Name: programas_cursos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('programas_cursos_id_seq', 52, true);


--
-- Name: programas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('programas_id_seq', 1, true);


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
\.


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: emmaus
--

SELECT pg_catalog.setval('users_id_seq', 1, false);


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
-- Name: trg_cursos_updated_at; Type: TRIGGER; Schema: public; Owner: emmaus
--

CREATE TRIGGER trg_cursos_updated_at BEFORE UPDATE ON cursos FOR EACH ROW EXECUTE PROCEDURE plg_set_updated_at();


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
-- Name: contactos; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE contactos FROM PUBLIC;
REVOKE ALL ON TABLE contactos FROM emmaus;
GRANT ALL ON TABLE contactos TO emmaus;
GRANT ALL ON TABLE contactos TO emmaus_estudiantes;
GRANT ALL ON TABLE contactos TO emmaus_buc_estudiantes;
GRANT ALL ON TABLE contactos TO emmaus_bar_estudiantes;
GRANT ALL ON TABLE contactos TO emmaus_admin;


--
-- Name: contactos_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE contactos_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE contactos_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE contactos_id_seq TO emmaus;
GRANT ALL ON SEQUENCE contactos_id_seq TO emmaus_admin;


--
-- Name: cursos; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE cursos FROM PUBLIC;
REVOKE ALL ON TABLE cursos FROM emmaus;
GRANT ALL ON TABLE cursos TO emmaus;
GRANT ALL ON TABLE cursos TO emmaus_estudiantes;
GRANT ALL ON TABLE cursos TO emmaus_buc_estudiantes;
GRANT ALL ON TABLE cursos TO emmaus_bar_estudiantes;
GRANT ALL ON TABLE cursos TO emmaus_admin;


--
-- Name: cursos_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE cursos_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE cursos_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE cursos_id_seq TO emmaus;
GRANT ALL ON SEQUENCE cursos_id_seq TO emmaus_admin;


--
-- Name: estudiantes; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE estudiantes FROM PUBLIC;
REVOKE ALL ON TABLE estudiantes FROM emmaus;
GRANT ALL ON TABLE estudiantes TO emmaus;
GRANT ALL ON TABLE estudiantes TO emmaus_estudiantes;
GRANT ALL ON TABLE estudiantes TO emmaus_buc_estudiantes;
GRANT ALL ON TABLE estudiantes TO emmaus_bar_estudiantes;
GRANT ALL ON TABLE estudiantes TO emmaus_admin;


--
-- Name: estudiantes_cursos; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE estudiantes_cursos FROM PUBLIC;
REVOKE ALL ON TABLE estudiantes_cursos FROM emmaus;
GRANT ALL ON TABLE estudiantes_cursos TO emmaus;
GRANT ALL ON TABLE estudiantes_cursos TO emmaus_estudiantes;
GRANT ALL ON TABLE estudiantes_cursos TO emmaus_buc_estudiantes;
GRANT ALL ON TABLE estudiantes_cursos TO emmaus_bar_estudiantes;
GRANT ALL ON TABLE estudiantes_cursos TO emmaus_admin;


--
-- Name: estudiantes_cursos_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE estudiantes_cursos_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE estudiantes_cursos_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE estudiantes_cursos_id_seq TO emmaus;
GRANT ALL ON SEQUENCE estudiantes_cursos_id_seq TO emmaus_admin;


--
-- Name: estudiantes_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE estudiantes_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE estudiantes_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE estudiantes_id_seq TO emmaus;
GRANT ALL ON SEQUENCE estudiantes_id_seq TO emmaus_admin;


--
-- Name: estudiantes_programas; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE estudiantes_programas FROM PUBLIC;
REVOKE ALL ON TABLE estudiantes_programas FROM emmaus;
GRANT ALL ON TABLE estudiantes_programas TO emmaus;
GRANT ALL ON TABLE estudiantes_programas TO emmaus_bar_estudiantes;
GRANT ALL ON TABLE estudiantes_programas TO emmaus_admin;


--
-- Name: niveles; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE niveles FROM PUBLIC;
REVOKE ALL ON TABLE niveles FROM emmaus;
GRANT ALL ON TABLE niveles TO emmaus;
GRANT ALL ON TABLE niveles TO emmaus_estudiantes;
GRANT ALL ON TABLE niveles TO emmaus_buc_estudiantes;
GRANT ALL ON TABLE niveles TO emmaus_bar_estudiantes;
GRANT ALL ON TABLE niveles TO emmaus_admin;


--
-- Name: niveles_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE niveles_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE niveles_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE niveles_id_seq TO emmaus;
GRANT ALL ON SEQUENCE niveles_id_seq TO emmaus_admin;


--
-- Name: niveles_programas; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE niveles_programas FROM PUBLIC;
REVOKE ALL ON TABLE niveles_programas FROM emmaus;
GRANT ALL ON TABLE niveles_programas TO emmaus;
GRANT ALL ON TABLE niveles_programas TO emmaus_estudiantes;
GRANT ALL ON TABLE niveles_programas TO emmaus_buc_estudiantes;
GRANT ALL ON TABLE niveles_programas TO emmaus_admin;
GRANT ALL ON TABLE niveles_programas TO emmaus_bar_estudiantes;


--
-- Name: niveles_programas_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE niveles_programas_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE niveles_programas_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE niveles_programas_id_seq TO emmaus;
GRANT ALL ON SEQUENCE niveles_programas_id_seq TO emmaus_estudiantes;
GRANT ALL ON SEQUENCE niveles_programas_id_seq TO emmaus_buc_estudiantes;
GRANT ALL ON SEQUENCE niveles_programas_id_seq TO emmaus_admin;


--
-- Name: observaciones_estudiantes; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE observaciones_estudiantes FROM PUBLIC;
REVOKE ALL ON TABLE observaciones_estudiantes FROM emmaus;
GRANT ALL ON TABLE observaciones_estudiantes TO emmaus;
GRANT ALL ON TABLE observaciones_estudiantes TO emmaus_bar_estudiantes;
GRANT ALL ON TABLE observaciones_estudiantes TO emmaus_admin;


--
-- Name: observaciones_estudiantes_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE observaciones_estudiantes_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE observaciones_estudiantes_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE observaciones_estudiantes_id_seq TO emmaus;
GRANT ALL ON SEQUENCE observaciones_estudiantes_id_seq TO emmaus_admin;


--
-- Name: programas; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE programas FROM PUBLIC;
REVOKE ALL ON TABLE programas FROM emmaus;
GRANT ALL ON TABLE programas TO emmaus;
GRANT ALL ON TABLE programas TO emmaus_estudiantes;
GRANT ALL ON TABLE programas TO emmaus_buc_estudiantes;
GRANT ALL ON TABLE programas TO emmaus_admin;
GRANT ALL ON TABLE programas TO emmaus_bar_estudiantes;


--
-- Name: programas_asignaciones; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE programas_asignaciones FROM PUBLIC;
REVOKE ALL ON TABLE programas_asignaciones FROM emmaus;
GRANT ALL ON TABLE programas_asignaciones TO emmaus;
GRANT ALL ON TABLE programas_asignaciones TO emmaus_bar_estudiantes;
GRANT ALL ON TABLE programas_asignaciones TO emmaus_admin;


--
-- Name: programas_asignaciones_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE programas_asignaciones_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE programas_asignaciones_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE programas_asignaciones_id_seq TO emmaus;
GRANT ALL ON SEQUENCE programas_asignaciones_id_seq TO emmaus_admin;


--
-- Name: programas_cursos; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE programas_cursos FROM PUBLIC;
REVOKE ALL ON TABLE programas_cursos FROM emmaus;
GRANT ALL ON TABLE programas_cursos TO emmaus;
GRANT ALL ON TABLE programas_cursos TO emmaus_estudiantes;
GRANT ALL ON TABLE programas_cursos TO emmaus_buc_estudiantes;
GRANT ALL ON TABLE programas_cursos TO emmaus_admin;
GRANT ALL ON TABLE programas_cursos TO emmaus_bar_estudiantes;


--
-- Name: programas_cursos_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE programas_cursos_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE programas_cursos_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE programas_cursos_id_seq TO emmaus;
GRANT ALL ON SEQUENCE programas_cursos_id_seq TO emmaus_estudiantes;
GRANT ALL ON SEQUENCE programas_cursos_id_seq TO emmaus_buc_estudiantes;
GRANT ALL ON SEQUENCE programas_cursos_id_seq TO emmaus_admin;


--
-- Name: programas_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE programas_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE programas_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE programas_id_seq TO emmaus;
GRANT ALL ON SEQUENCE programas_id_seq TO emmaus_estudiantes;
GRANT ALL ON SEQUENCE programas_id_seq TO emmaus_buc_estudiantes;
GRANT ALL ON SEQUENCE programas_id_seq TO emmaus_admin;


--
-- Name: programas_prerequisitos; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON TABLE programas_prerequisitos FROM PUBLIC;
REVOKE ALL ON TABLE programas_prerequisitos FROM emmaus;
GRANT ALL ON TABLE programas_prerequisitos TO emmaus;
GRANT ALL ON TABLE programas_prerequisitos TO emmaus_estudiantes;
GRANT ALL ON TABLE programas_prerequisitos TO emmaus_buc_estudiantes;
GRANT ALL ON TABLE programas_prerequisitos TO emmaus_admin;
GRANT ALL ON TABLE programas_prerequisitos TO emmaus_bar_estudiantes;


--
-- Name: programas_prerequisitos_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE programas_prerequisitos_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE programas_prerequisitos_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE programas_prerequisitos_id_seq TO emmaus;
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
GRANT ALL ON TABLE users TO emmaus_buc_estudiantes;
GRANT ALL ON TABLE users TO emmaus_bar_estudiantes;
GRANT ALL ON TABLE users TO emmaus_admin;


--
-- Name: users_id_seq; Type: ACL; Schema: public; Owner: emmaus
--

REVOKE ALL ON SEQUENCE users_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE users_id_seq FROM emmaus;
GRANT ALL ON SEQUENCE users_id_seq TO emmaus;
GRANT ALL ON SEQUENCE users_id_seq TO emmaus_admin;


--
-- PostgreSQL database dump complete
--
