-- ============================================
-- Configuration
-- ============================================
SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

CREATE SCHEMA IF NOT EXISTS public;
COMMENT ON SCHEMA public IS 'standard public schema';

SET default_tablespace = '';
SET default_table_access_method = heap;

-- ============================================
-- Tables & Séquences (ordre de dépendance)
-- ============================================

-- Table héritée : 
CREATE TABLE public.historytable (
    createdat TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updatedat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdby UUID,
    updatedby UUID,
    isactive BOOLEAN DEFAULT TRUE NOT NULL
);

-- Role
CREATE TABLE public.role (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(50) NOT NULL UNIQUE
);

-- State
CREATE TABLE public.state (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Address
CREATE TABLE public.address (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    streetnumber INTEGER NOT NULL,
    streetletter VARCHAR(20),
    streetname VARCHAR(200) NOT NULL,
    postcode VARCHAR(20) NOT NULL,
    state VARCHAR(80),
    city VARCHAR(80) NOT NULL,
    country VARCHAR(80)
)INHERITS (public.historytable);

-- Client
CREATE TABLE public.client (
    siret VARCHAR(14) PRIMARY KEY,
    companyname VARCHAR(100) NOT NULL,
    workfield VARCHAR(100),
    contactfirstname VARCHAR(80) NOT NULL,
    contactlastname VARCHAR(80) NOT NULL,
    contactemail VARCHAR(100) NOT NULL,
    contactphone VARCHAR(20)
)INHERITS (public.historytable);

-- Users : 
CREATE TABLE public.users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    firstname VARCHAR(80) NOT NULL,
    lastname VARCHAR(80) NOT NULL,
    jobtitle VARCHAR(100),
    fieldofwork VARCHAR(100),
    degree VARCHAR[],
    role_id UUID REFERENCES public.role(id) ON DELETE SET NULL
)INHERITS (public.historytable);

-- Information
CREATE TABLE public.information (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    type VARCHAR(50), --Message ou Notification
    text VARCHAR(300),
    isread BOOLEAN DEFAULT FALSE NOT NULL,
    user_id UUID REFERENCES public.users(id) ON DELETE SET NULL
)INHERITS (public.historytable);

-- Project
CREATE TABLE public.project (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(80) NOT NULL,
    description VARCHAR(200),
    begindate TIMESTAMP NOT NULL,
    theoreticaldeadline TIMESTAMP NOT NULL,
    realdeadline TIMESTAMP,
    effortcalculated NUMERIC(10, 2),
    template BOOLEAN,
    client_id VARCHAR(14) REFERENCES public.client(siret) ON DELETE SET NULL,
    project_manager_id UUID REFERENCES public.users(id) ON DELETE SET NULL,
    state_id UUID REFERENCES public.state(id) ON DELETE SET NULL
)INHERITS (public.historytable);

-- Task
CREATE TABLE public.task (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(80) NOT NULL,
    description VARCHAR(200),
    fieldofwork VARCHAR(100),
    type VARCHAR(60) NOT NULL,
    format VARCHAR(80),
    priority VARCHAR(60),
    difficulty VARCHAR(60),
    effortrequired NUMERIC(10, 2) NOT NULL,
    effortmade NUMERIC(10, 2),
    begindate TIMESTAMP NOT NULL,
    theoreticalenddate TIMESTAMP NOT NULL,
    realenddate TIMESTAMP,
    template BOOLEAN,
    project_id UUID REFERENCES public.project(id) ON DELETE SET NULL,
    state_id UUID REFERENCES public.state(id) ON DELETE SET NULL
)INHERITS (public.historytable);

-- Absence
CREATE TABLE public.absence (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
    reason VARCHAR(255),
    startdate DATE NOT NULL,
    enddate DATE NOT NULL,
    createdat TIMESTAMP NOT NULL DEFAULT NOW(),
    CONSTRAINT absence_dates_check CHECK (enddate >= startdate)
);

-- Tables de liaison
CREATE TABLE public.clientaddressREL (
    siret VARCHAR(14) NOT NULL,
    address_id UUID NOT NULL,
    PRIMARY KEY (siret, address_id)
);

CREATE TABLE public.useraddressREL (
    user_id UUID NOT NULL,
    address_id UUID NOT NULL,
    PRIMARY KEY (user_id, address_id)
);

CREATE TABLE public.usertaskREL (
    user_id UUID NOT NULL,
    task_id UUID NOT NULL,
    PRIMARY KEY (user_id, task_id)
);

-- ============================================
-- Index
-- ============================================

CREATE INDEX idx_project_manager ON public.project USING btree (project_manager_id);
CREATE INDEX idx_project_client ON public.project USING btree (client_id);
CREATE INDEX idx_task_project ON public.task USING btree (project_id);
CREATE INDEX idx_task_state ON public.task USING btree (state_id);
CREATE INDEX idx_project_state ON public.project USING btree (state_id);
CREATE INDEX idx_clientaddress_address ON public.clientaddressREL USING btree (address_id);
CREATE INDEX idx_useraddress_address ON public.useraddressREL USING btree (address_id);
CREATE INDEX idx_usertask_task ON public.usertaskREL USING btree (task_id);
CREATE INDEX idx_information_user ON public.information USING btree (user_id);
CREATE INDEX idx_absence_user_id ON public.absence USING btree (user_id);
CREATE INDEX idx_absence_startdate ON public.absence USING btree (startdate);
CREATE INDEX idx_absence_enddate ON public.absence USING btree (enddate);


-- ============================================
-- Clés étrangères
-- ============================================

ALTER TABLE public.usertaskREL ADD CONSTRAINT usertaskrel_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;
ALTER TABLE public.usertaskREL ADD CONSTRAINT usertaskrel_task_id_fkey FOREIGN KEY (task_id) REFERENCES public.task(id) ON DELETE CASCADE;

ALTER TABLE public.clientaddressREL ADD CONSTRAINT clientaddressrel_siret_fkey FOREIGN KEY (siret) REFERENCES public.client(siret) ON DELETE CASCADE;
ALTER TABLE public.clientaddressREL ADD CONSTRAINT clientaddressrel_address_id_fkey FOREIGN KEY (address_id) REFERENCES public.address(id) ON DELETE CASCADE;

ALTER TABLE public.useraddressREL ADD CONSTRAINT useraddressrel_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;
ALTER TABLE public.useraddressREL ADD CONSTRAINT useraddressrel_address_id_fkey FOREIGN KEY (address_id) REFERENCES public.address(id) ON DELETE CASCADE;

-- ============================================
-- Données initiales
-- ============================================

-- Index sur users(role_id) pour accélérer les filtrages par rôle
CREATE INDEX idx_users_role ON public.users USING btree (role_id);

-- Rôles : 
INSERT INTO public.role (id, name) VALUES
    ('0b89030f-8785-4429-ab48-33598f74d749', 'PDG'),
    ('9c6faf31-f71c-43d8-a4d2-a1b0cba65c30', 'ADMIN'),
    ('0f331b53-19ec-477a-a0cd-41ad29b06d0e', 'CDP'),
    ('903210fc-e634-49b3-9306-a6ea3bd3be81', 'USER');

-- Statuts : 
INSERT INTO public.state (id, name) VALUES
    ('a1000000-0000-0000-0000-000000000001', 'En attente'),
    ('a1000000-0000-0000-0000-000000000002', 'En cours'),
    ('a1000000-0000-0000-0000-000000000003', 'Terminé'),
    ('a1000000-0000-0000-0000-000000000004', 'Retardé'),
    ('a1000000-0000-0000-0000-000000000005', 'Annulé');

-- Utilisateurs de test (mot de passe commun : Test@1234, hashé en Argon2i) :
INSERT INTO public.users (firstname, lastname, email, password, role_id) VALUES
    ('Valentin', 'Delnatte', 'valentin@kentec.com', '$argon2i$v=19$m=65536,t=4,p=1$eS5EWloxbU9EbXVvdDFOaA$ZbrLCT8l/C3yasHTVNhF2ysAEgGR9PREdqZk0QvsdtA', '9c6faf31-f71c-43d8-a4d2-a1b0cba65c30'),
    ('Antoine', 'Gallo', 'antoine@kentec.com', '$argon2i$v=19$m=65536,t=4,p=1$c00uRkd2eFFGZVpzNlB1Nw$OfswhgLoqW1JmWArks8RgwYRQpXaX3pWSlusKdsQvow', '0f331b53-19ec-477a-a0cd-41ad29b06d0e'),
    ('Zak', 'Ben El Gharib', 'zak@kentec.com', '$argon2i$v=19$m=65536,t=4,p=1$Y3BGY3lwZ1RqSjFnM3dneQ$idT1dnI4XmiCIAKwImJdEt5cIiwTxRqfotmiFrKOuOE', '903210fc-e634-49b3-9306-a6ea3bd3be81');

-- ============================================
-- Comptes examinateurs (un par rôle)
-- Mot de passe commun : Exam@2024
-- ============================================
INSERT INTO public.users (firstname, lastname, email, password, role_id) VALUES
    ('Examinateur', 'PDG',   'pdg@kentec.com',   '$argon2i$v=19$m=65536,t=4,p=1$QXhIeGdIM2RpZG1EREJVag$oWzb0E0c6WpsPmZq0clGwq9MQPKQmJyreQIJR5xxWTY', '0b89030f-8785-4429-ab48-33598f74d749'),
    ('Examinateur', 'ADMIN', 'admin@kentec.com', '$argon2i$v=19$m=65536,t=4,p=1$eGI5N1Q5Smd2NWtPN1BKNA$Rql4bSQx+QalNwjr8xNa7hGwu72j95TxH/bTgKxWX5I', '9c6faf31-f71c-43d8-a4d2-a1b0cba65c30'),
    ('Examinateur', 'CDP',   'cdp@kentec.com',   '$argon2i$v=19$m=65536,t=4,p=1$Wk1oRjB5L1BGSW1LSTJtbQ$ZNWGPIQrrfwsQDIJMSMhXYN4lPtRn+oJa/N5P12boD0', '0f331b53-19ec-477a-a0cd-41ad29b06d0e'),
    ('Examinateur', 'USER',  'user@kentec.com',  '$argon2i$v=19$m=65536,t=4,p=1$MndYLnIuMllQOFBmN0dLcA$XrzXFjYxX4j9KCLRAGqG/LDUt7ErJCcdaDSXU+2mel8', '903210fc-e634-49b3-9306-a6ea3bd3be81');
