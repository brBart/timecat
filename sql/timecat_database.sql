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
-- Name: client; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE client (
    clientid integer NOT NULL,
    client_name text,
    email text,
    address1 text,
    address2 text,
    city text,
    zipcode integer,
    contact_first text,
    contact_last text,
    referrer text,
    referrer_type text,
    current_client boolean,
    state text,
    fee_adjust double precision DEFAULT 1
);


--
-- Name: client_clientid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE client_clientid_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: client_clientid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE client_clientid_seq OWNED BY client.clientid;


--
-- Name: company; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE company (
    company_name text,
    address1 text,
    address2 text,
    phone text,
    email text,
    footer1 text,
    footer2 text,
    logo_file text
);


--
-- Name: invoices; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE invoices (
    client_name text,
    sent boolean,
    date date,
    amount numeric(9,2),
    amount_paid numeric(9,2),
    due_date date,
    identifier text,
    number integer NOT NULL,
    is_deleted boolean DEFAULT false
);


--
-- Name: invoices_number_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE invoices_number_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: invoices_number_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE invoices_number_seq OWNED BY invoices.number;


--
-- Name: matter; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE matter (
    matterid smallint,
    client_name text,
    matter_description text
);


--
-- Name: timeentry; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE timeentry (
    entryid integer NOT NULL,
    duration numeric(3,1),
    matter_id text,
    invoice_no integer,
    writeoff double precision,
    date date,
    client_name text,
    description text,
    notes text,
    flatfee_item numeric(9,2),
    timekeeper_email text,
    start_time time without time zone,
    end_time time without time zone
);


--
-- Name: timeentry_entryid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE timeentry_entryid_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: timeentry_entryid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE timeentry_entryid_seq OWNED BY timeentry.entryid;


--
-- Name: timekeeper; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE timekeeper (
    timekeeperid integer NOT NULL,
    first_name text,
    last_name text,
    login_id text,
    rate numeric(7,2),
    active boolean,
    email text,
    company_name text,
    administrator boolean
);


--
-- Name: timekeeper_timekeeperid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE timekeeper_timekeeperid_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: timekeeper_timekeeperid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE timekeeper_timekeeperid_seq OWNED BY timekeeper.timekeeperid;


--
-- Name: clientid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY client ALTER COLUMN clientid SET DEFAULT nextval('client_clientid_seq'::regclass);


--
-- Name: number; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY invoices ALTER COLUMN number SET DEFAULT nextval('invoices_number_seq'::regclass);


--
-- Name: entryid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY timeentry ALTER COLUMN entryid SET DEFAULT nextval('timeentry_entryid_seq'::regclass);


--
-- Name: timekeeperid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY timekeeper ALTER COLUMN timekeeperid SET DEFAULT nextval('timekeeper_timekeeperid_seq'::regclass);


--
-- Name: client_client_name_key; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY client
    ADD CONSTRAINT client_client_name_key UNIQUE (client_name);


--
-- Name: company_company_name_key; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY company
    ADD CONSTRAINT company_company_name_key UNIQUE (company_name);


--
-- Name: timekeeper_email_key; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY timekeeper
    ADD CONSTRAINT timekeeper_email_key UNIQUE (email);


--
-- Name: timekeeper_login_id_key; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY timekeeper
    ADD CONSTRAINT timekeeper_login_id_key UNIQUE (login_id);


--
-- Name: clientfk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY invoices
    ADD CONSTRAINT clientfk FOREIGN KEY (client_name) REFERENCES client(client_name) ON UPDATE CASCADE;


--
-- Name: fk_email; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY timeentry
    ADD CONSTRAINT fk_email FOREIGN KEY (timekeeper_email) REFERENCES timekeeper(email) ON UPDATE CASCADE;


--
-- Name: matter_client_name_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY matter
    ADD CONSTRAINT matter_client_name_fkey FOREIGN KEY (client_name) REFERENCES client(client_name) ON UPDATE CASCADE;


--
-- Name: timeentry_client_name_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY timeentry
    ADD CONSTRAINT timeentry_client_name_fkey FOREIGN KEY (client_name) REFERENCES client(client_name) ON UPDATE CASCADE;


--
-- Name: timekeeper_company_name_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY timekeeper
    ADD CONSTRAINT timekeeper_company_name_fkey FOREIGN KEY (company_name) REFERENCES company(company_name) ON UPDATE CASCADE;


--
-- Name: public; Type: ACL; Schema: -; Owner: -
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

