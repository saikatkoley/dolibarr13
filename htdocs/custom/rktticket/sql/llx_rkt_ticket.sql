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

CREATE TABLE llx_rkt_ticket_dict(
	rowid INTEGER AUTO_INCREMENT PRIMARY KEY,
	dict_name VARCHAR(100) NOT NULL,
	libelle VARCHAR(100) NOT NULL,
        description VARCHAR(255) NULL,
        active INTEGER DEFAULT 1 NOT NULL
);

CREATE TABLE llx_rkt_ticket(
	rowid INTEGER AUTO_INCREMENT PRIMARY KEY,
        entity INTEGER DEFAULT 1 NOT NULL,
        ref VARCHAR(30) NOT NULL, -- numero de suivi
        fk_soc INTEGER DEFAULT NULL, -- id client
        fk_type INTEGER NOT NULL,
        fk_category INTEGER NOT NULL,
        fk_severity INTEGER NOT NULL,
	sujet VARCHAR(255) NOT NULL,
	message TEXT NOT NULL,
        created_by INTEGER NOT NULL,
        assigned_to INTEGER NULL,
        creation_date DATETIME NOT NULL,
        model_pdf VARCHAR(255) DEFAULT NULL,
        status INTEGER DEFAULT 0 NOT NULL
);
