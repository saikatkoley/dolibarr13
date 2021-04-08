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

ALTER TABLE llx_rkt_ticket_dict ADD UNIQUE INDEX uk_libelle (libelle);

ALTER TABLE llx_rkt_ticket ADD UNIQUE INDEX uk_ref (ref);
ALTER TABLE llx_rkt_ticket ADD CONSTRAINT fk_ticket_fk_soc FOREIGN KEY (fk_soc) REFERENCES llx_societe (rowid);

ALTER TABLE llx_rkt_ticket ADD CONSTRAINT fk_ticket_type FOREIGN KEY (fk_type) REFERENCES llx_rkt_ticket_dict (rowid);
ALTER TABLE llx_rkt_ticket ADD CONSTRAINT fk_ticket_category FOREIGN KEY (fk_category) REFERENCES llx_rkt_ticket_dict (rowid);
ALTER TABLE llx_ticket ADD CONSTRAINT fk_ticket_severity FOREIGN KEY (fk_severity) REFERENCES llx_rkt_ticket_dict (rowid);

ALTER TABLE llx_rkt_ticket ADD CONSTRAINT fk_ticket_created_by FOREIGN KEY (created_by) REFERENCES llx_user (rowid);
ALTER TABLE llx_rkt_ticket ADD CONSTRAINT fk_ticket_assigned_to FOREIGN KEY (assigned_to) REFERENCES llx_user (rowid);
