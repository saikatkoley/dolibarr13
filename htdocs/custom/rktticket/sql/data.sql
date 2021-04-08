-- <one line to give the program's name and a brief idea of what it does.>
-- Copyright (C) <year>  <name of author>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see <http://www.gnu.org/licenses/>.

-- french dict (do not change/translate 'type', 'category', 'severity' strings)
--INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) VALUES ('type', 'Demande assistance', 'Assistance...', 1);
--INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) VALUES ('type', 'Dysfonctionnement', 'Dysfonctionnement matériel ou logiciel', 1);
--INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) VALUES ('type', 'Incident', 'Incident technique', 1);
--INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) VALUES ('type', 'Demande information', 'Vous avez une question?', 1);

--INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) VALUES ('category', 'Développement', '', 1);
--INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) VALUES ('category', 'Maintenance', '', 1);
--INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) VALUES ('category', 'Support technique', '', 1);

--INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) VALUES ('severity', 'Bas', '', 1);
--INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) VALUES ('severity', 'Normal', '', 1);
--INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) VALUES ('severity', 'Critique / Bloquant', '', 1);

-- if default language is french (do not change/translate 'type', 'category', 'severity' strings)
INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) SELECT 'type', 'Demande assistance', 'Assistance...', 1 FROM DUAL WHERE (SELECT value FROM llx_const WHERE name = 'MAIN_LANG_DEFAULT') LIKE 'fr%';
INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) SELECT 'type', 'Dysfonctionnement', 'Dysfonctionnement matériel ou logiciel', 1 FROM DUAL WHERE (SELECT value FROM llx_const WHERE name = 'MAIN_LANG_DEFAULT') LIKE 'fr%';
INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) SELECT 'type', 'Incident', 'Incident technique', 1 FROM DUAL WHERE (SELECT value FROM llx_const WHERE name = 'MAIN_LANG_DEFAULT') LIKE 'fr%';
INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) SELECT 'type', 'Demande information', 'Vous avez une question?', 1  FROM DUAL WHERE (SELECT value FROM llx_const WHERE name = 'MAIN_LANG_DEFAULT') LIKE 'fr%';

INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) SELECT 'category', 'Développement', '', 1 FROM DUAL WHERE (SELECT value FROM llx_const WHERE name = 'MAIN_LANG_DEFAULT') LIKE 'fr%';
INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) SELECT 'category', 'Maintenance', '', 1 FROM DUAL WHERE (SELECT value FROM llx_const WHERE name = 'MAIN_LANG_DEFAULT') LIKE 'fr%';
INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) SELECT 'category', 'Support technique', '', 1 FROM DUAL WHERE (SELECT value FROM llx_const WHERE name = 'MAIN_LANG_DEFAULT') LIKE 'fr%';

INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) SELECT 'severity', 'Bas', '', 1 FROM DUAL WHERE (SELECT value FROM llx_const WHERE name = 'MAIN_LANG_DEFAULT') LIKE 'fr%';
INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) SELECT 'severity', 'Normal', '', 1 FROM DUAL WHERE (SELECT value FROM llx_const WHERE name = 'MAIN_LANG_DEFAULT') LIKE 'fr%';
INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) SELECT 'severity', 'Critique / Bloquant', '', 1 FROM DUAL WHERE (SELECT value FROM llx_const WHERE name = 'MAIN_LANG_DEFAULT') LIKE 'fr%';

-- if default language different from french -> use english
INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) SELECT 'type', 'Assistance request', 'Assistance...', 1 FROM DUAL WHERE (SELECT value FROM llx_const WHERE name = 'MAIN_LANG_DEFAULT') NOT LIKE 'fr%';
INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) SELECT 'type', 'Dysfunction', 'Hardware or software dysfunction', 1 FROM DUAL FROM DUAL WHERE (SELECT value FROM llx_const WHERE name = 'MAIN_LANG_DEFAULT') NOT LIKE 'fr%';
INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) SELECT 'type', 'Incident', 'Technical Incident', 1 FROM DUAL WHERE (SELECT value FROM llx_const WHERE name = 'MAIN_LANG_DEFAULT') NOT LIKE 'fr%';
INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) SELECT 'type', 'Information request', 'You have a question?', 1 FROM DUAL WHERE (SELECT value FROM llx_const WHERE name = 'MAIN_LANG_DEFAULT') NOT LIKE 'fr%';

INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) SELECT 'category', 'Development', '', 1 FROM DUAL WHERE (SELECT value FROM llx_const WHERE name = 'MAIN_LANG_DEFAULT') NOT LIKE 'fr%';
INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) SELECT 'category', 'Maintenance', '', 1 FROM DUAL WHERE (SELECT value FROM llx_const WHERE name = 'MAIN_LANG_DEFAULT') NOT LIKE 'fr%';
INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) SELECT 'category', 'Technical support', '', 1 FROM DUAL WHERE (SELECT value FROM llx_const WHERE name = 'MAIN_LANG_DEFAULT') NOT LIKE 'fr%';

INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) SELECT 'severity', 'Low', '', 1 FROM DUAL WHERE (SELECT value FROM llx_const WHERE name = 'MAIN_LANG_DEFAULT') NOT LIKE 'fr%';
INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) SELECT 'severity', 'Normal', '', 1 FROM DUAL WHERE (SELECT value FROM llx_const WHERE name = 'MAIN_LANG_DEFAULT') NOT LIKE 'fr%';
INSERT INTO llx_rkt_ticket_dict (dict_name, libelle, description, active) SELECT 'severity', 'Critical / Blocking', '', 1 FROM DUAL WHERE (SELECT value FROM llx_const WHERE name = 'MAIN_LANG_DEFAULT') NOT LIKE 'fr%';

-- Document model
INSERT INTO llx_document_model(nom, type) VALUES ('einstein', 'ticket');
