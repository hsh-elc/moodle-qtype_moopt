-- VARIANTS 100

-- DBS1.7.1.a::0::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 100 oder 122, deren Telefonnummer auf 876 endet.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (100, 122)
AND phone_number LIKE '%876';

-- DBS1.7.1.a::1::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 101 oder 100, deren Telefonnummer 5.1 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (101, 100)
AND phone_number LIKE '%5.1%';

-- DBS1.7.1.a::2::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 147 oder 122, deren Telefonnummer 27. enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (147, 122)
AND phone_number LIKE '%27.%';

-- DBS1.7.1.a::3::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 145 oder 108, deren Telefonnummer auf 268 endet.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (145, 108)
AND phone_number LIKE '%268';

-- DBS1.7.1.a::4::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 147 oder 123, deren Telefonnummer mit 650 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (147, 123)
AND phone_number LIKE '650%';

-- DBS1.7.1.a::5::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 100 oder 146, deren Telefonnummer mit 011 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (100, 146)
AND phone_number LIKE '011%';

-- DBS1.7.1.a::6::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 148 oder 124, deren Telefonnummer 50. enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (148, 124)
AND phone_number LIKE '%50.%';

-- DBS1.7.1.a::7::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 121 oder 145, deren Telefonnummer 50. enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (121, 145)
AND phone_number LIKE '%50.%';

-- DBS1.7.1.a::8::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 101 oder 146, deren Telefonnummer .44 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (101, 146)
AND phone_number LIKE '%.44%';

-- DBS1.7.1.a::9::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 146 oder 120, deren Telefonnummer 011 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (146, 120)
AND phone_number LIKE '%011%';

-- DBS1.7.1.a::10::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 146 oder 122, deren Telefonnummer 292 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (146, 122)
AND phone_number LIKE '%292%';

-- DBS1.7.1.a::11::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 147 oder 122, deren Telefonnummer mit 650 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (147, 122)
AND phone_number LIKE '650%';

-- DBS1.7.1.a::12::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 121 oder 103, deren Telefonnummer mit 650 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (121, 103)
AND phone_number LIKE '650%';

-- DBS1.7.1.a::13::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 201 oder 122, deren Telefonnummer auf 876 endet.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (201, 122)
AND phone_number LIKE '%876';

-- DBS1.7.1.a::14::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 145 oder 146, deren Telefonnummer 650 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (145, 146)
AND phone_number LIKE '%650%';

-- DBS1.7.1.a::15::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 122 oder 102, deren Telefonnummer 90. enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (122, 102)
AND phone_number LIKE '%90.%';

-- DBS1.7.1.a::16::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 122 oder 114, deren Telefonnummer mit 650 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (122, 114)
AND phone_number LIKE '650%';

-- DBS1.7.1.a::17::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 114 oder 201, deren Telefonnummer 515 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (114, 201)
AND phone_number LIKE '%515%';

-- DBS1.7.1.a::18::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 123 oder 124, deren Telefonnummer mit 650 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (123, 124)
AND phone_number LIKE '650%';

-- DBS1.7.1.a::19::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 121 oder 102, deren Telefonnummer mit 650 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (121, 102)
AND phone_number LIKE '650%';

-- DBS1.7.1.a::20::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 103 oder 201, deren Telefonnummer 0.4 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (103, 201)
AND phone_number LIKE '%0.4%';

-- DBS1.7.1.a::21::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 120 oder 101, deren Telefonnummer auf 444 endet.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (120, 101)
AND phone_number LIKE '%444';

-- DBS1.7.1.a::22::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 145 oder 147, deren Telefonnummer mit 011 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (145, 147)
AND phone_number LIKE '011%';

-- DBS1.7.1.a::23::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 124 oder 148, deren Telefonnummer mit 011 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (124, 148)
AND phone_number LIKE '011%';

-- DBS1.7.1.a::24::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 201 oder 100, deren Telefonnummer auf 268 endet.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (201, 100)
AND phone_number LIKE '%268';

-- DBS1.7.1.a::25::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 121 oder 147, deren Telefonnummer 0.1 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (121, 147)
AND phone_number LIKE '%0.1%';

-- DBS1.7.1.a::26::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 201 oder 103, deren Telefonnummer mit 590 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (201, 103)
AND phone_number LIKE '590%';

-- DBS1.7.1.a::27::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 101 oder 149, deren Telefonnummer mit 011 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (101, 149)
AND phone_number LIKE '011%';

-- DBS1.7.1.a::28::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 123 oder 120, deren Telefonnummer auf 876 endet.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (123, 120)
AND phone_number LIKE '%876';

-- DBS1.7.1.a::29::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 114 oder 101, deren Telefonnummer .12 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (114, 101)
AND phone_number LIKE '%.12%';

-- DBS1.7.1.a::30::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 201 oder 100, deren Telefonnummer mit 011 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (201, 100)
AND phone_number LIKE '011%';

-- DBS1.7.1.a::31::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 122 oder 102, deren Telefonnummer auf 876 endet.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (122, 102)
AND phone_number LIKE '%876';

-- DBS1.7.1.a::32::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 123 oder 122, deren Telefonnummer auf 034 endet.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (123, 122)
AND phone_number LIKE '%034';

-- DBS1.7.1.a::33::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 145 oder 101, deren Telefonnummer mit 011 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (145, 101)
AND phone_number LIKE '011%';

-- DBS1.7.1.a::34::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 201 oder 147, deren Telefonnummer .52 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (201, 147)
AND phone_number LIKE '%.52%';

-- DBS1.7.1.a::35::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 146 oder 101, deren Telefonnummer mit 011 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (146, 101)
AND phone_number LIKE '011%';

-- DBS1.7.1.a::36::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 120 oder 148, deren Telefonnummer mit 650 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (120, 148)
AND phone_number LIKE '650%';

-- DBS1.7.1.a::37::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 146 oder 120, deren Telefonnummer mit 011 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (146, 120)
AND phone_number LIKE '011%';

-- DBS1.7.1.a::38::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 114 oder 146, deren Telefonnummer 011 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (114, 146)
AND phone_number LIKE '%011%';

-- DBS1.7.1.a::39::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 103 oder 122, deren Telefonnummer mit 650 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (103, 122)
AND phone_number LIKE '650%';

-- DBS1.7.1.a::40::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 148 oder 108, deren Telefonnummer mit 011 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (148, 108)
AND phone_number LIKE '011%';

-- DBS1.7.1.a::41::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 149 oder 120, deren Telefonnummer mit 650 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (149, 120)
AND phone_number LIKE '650%';

-- DBS1.7.1.a::42::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 124 oder 101, deren Telefonnummer auf 844 endet.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (124, 101)
AND phone_number LIKE '%844';

-- DBS1.7.1.a::43::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 147 oder 201, deren Telefonnummer 23. enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (147, 201)
AND phone_number LIKE '%23.%';

-- DBS1.7.1.a::44::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 124 oder 149, deren Telefonnummer 292 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (124, 149)
AND phone_number LIKE '%292%';

-- DBS1.7.1.a::45::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 145 oder 102, deren Telefonnummer .12 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (145, 102)
AND phone_number LIKE '%.12%';

-- DBS1.7.1.a::46::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 147 oder 120, deren Telefonnummer mit 650 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (147, 120)
AND phone_number LIKE '650%';

-- DBS1.7.1.a::47::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 123 oder 100, deren Telefonnummer mit 011 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (123, 100)
AND phone_number LIKE '011%';

-- DBS1.7.1.a::48::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 201 oder 149, deren Telefonnummer mit 011 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (201, 149)
AND phone_number LIKE '011%';

-- DBS1.7.1.a::49::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 148 oder 103, deren Telefonnummer auf 568 endet.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (148, 103)
AND phone_number LIKE '%568';

-- DBS1.7.1.a::50::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 145 oder 147, deren Telefonnummer 011 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (145, 147)
AND phone_number LIKE '%011%';

-- DBS1.7.1.a::51::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 120 oder 101, deren Telefonnummer mit 515 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (120, 101)
AND phone_number LIKE '515%';

-- DBS1.7.1.a::52::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 123 oder 101, deren Telefonnummer mit 650 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (123, 101)
AND phone_number LIKE '650%';

-- DBS1.7.1.a::53::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 123 oder 148, deren Telefonnummer 50. enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (123, 148)
AND phone_number LIKE '%50.%';

-- DBS1.7.1.a::54::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 147 oder 114, deren Telefonnummer mit 011 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (147, 114)
AND phone_number LIKE '011%';

-- DBS1.7.1.a::55::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 120 oder 147, deren Telefonnummer 11. enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (120, 147)
AND phone_number LIKE '%11.%';

-- DBS1.7.1.a::56::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 146 oder 120, deren Telefonnummer mit 011 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (146, 120)
AND phone_number LIKE '011%';

-- DBS1.7.1.a::57::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 120 oder 146, deren Telefonnummer mit 011 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (120, 146)
AND phone_number LIKE '011%';

-- DBS1.7.1.a::58::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 114 oder 124, deren Telefonnummer mit 650 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (114, 124)
AND phone_number LIKE '650%';

-- DBS1.7.1.a::59::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 145 oder 100, deren Telefonnummer auf 018 endet.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (145, 100)
AND phone_number LIKE '%018';

-- DBS1.7.1.a::60::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 100 oder 102, deren Telefonnummer auf 268 endet.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (100, 102)
AND phone_number LIKE '%268';

-- DBS1.7.1.a::61::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 123 oder 102, deren Telefonnummer auf 234 endet.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (123, 102)
AND phone_number LIKE '%234';

-- DBS1.7.1.a::62::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 120 oder 114, deren Telefonnummer mit 650 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (120, 114)
AND phone_number LIKE '650%';

-- DBS1.7.1.a::63::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 122 oder 205, deren Telefonnummer mit 515 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (122, 205)
AND phone_number LIKE '515%';

-- DBS1.7.1.a::64::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 121 oder 149, deren Telefonnummer auf 234 endet.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (121, 149)
AND phone_number LIKE '%234';

-- DBS1.7.1.a::65::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 147 oder 121, deren Telefonnummer mit 650 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (147, 121)
AND phone_number LIKE '650%';

-- DBS1.7.1.a::66::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 120 oder 124, deren Telefonnummer auf 876 endet.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (120, 124)
AND phone_number LIKE '%876';

-- DBS1.7.1.a::67::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 121 oder 114, deren Telefonnummer auf 234 endet.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (121, 114)
AND phone_number LIKE '%234';

-- DBS1.7.1.a::68::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 205 oder 120, deren Telefonnummer mit 650 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (205, 120)
AND phone_number LIKE '650%';

-- DBS1.7.1.a::69::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 103 oder 146, deren Telefonnummer .44 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (103, 146)
AND phone_number LIKE '%.44%';

-- DBS1.7.1.a::70::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 123 oder 102, deren Telefonnummer .50 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (123, 102)
AND phone_number LIKE '%.50%';

-- DBS1.7.1.a::71::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 146 oder 114, deren Telefonnummer 5.7 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (146, 114)
AND phone_number LIKE '%5.7%';

-- DBS1.7.1.a::72::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 100 oder 123, deren Telefonnummer mit 011 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (100, 123)
AND phone_number LIKE '011%';

-- DBS1.7.1.a::73::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 122 oder 201, deren Telefonnummer mit 650 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (122, 201)
AND phone_number LIKE '650%';

-- DBS1.7.1.a::74::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 145 oder 147, deren Telefonnummer mit 011 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (145, 147)
AND phone_number LIKE '011%';

-- DBS1.7.1.a::75::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 148 oder 201, deren Telefonnummer 4.1 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (148, 201)
AND phone_number LIKE '%4.1%';

-- DBS1.7.1.a::76::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 121 oder 120, deren Telefonnummer auf 234 endet.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (121, 120)
AND phone_number LIKE '%234';

-- DBS1.7.1.a::77::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 100 oder 123, deren Telefonnummer .12 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (100, 123)
AND phone_number LIKE '%.12%';

-- DBS1.7.1.a::78::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 148 oder 123, deren Telefonnummer 650 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (148, 123)
AND phone_number LIKE '%650%';

-- DBS1.7.1.a::79::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 100 oder 120, deren Telefonnummer 507 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (100, 120)
AND phone_number LIKE '%507%';

-- DBS1.7.1.a::80::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 100 oder 121, deren Telefonnummer .44 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (100, 121)
AND phone_number LIKE '%.44%';

-- DBS1.7.1.a::81::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 147 oder 108, deren Telefonnummer 5.1 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (147, 108)
AND phone_number LIKE '%5.1%';

-- DBS1.7.1.a::82::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 108 oder 205, deren Telefonnummer .44 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (108, 205)
AND phone_number LIKE '%.44%';

-- DBS1.7.1.a::83::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 100 oder 201, deren Telefonnummer 515 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (100, 201)
AND phone_number LIKE '%515%';

-- DBS1.7.1.a::84::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 100 oder 124, deren Telefonnummer mit 011 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (100, 124)
AND phone_number LIKE '011%';

-- DBS1.7.1.a::85::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 145 oder 101, deren Telefonnummer auf 268 endet.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (145, 101)
AND phone_number LIKE '%268';

-- DBS1.7.1.a::86::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 148 oder 108, deren Telefonnummer auf 268 endet.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (148, 108)
AND phone_number LIKE '%268';

-- DBS1.7.1.a::87::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 124 oder 100, deren Telefonnummer 3.5 enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (124, 100)
AND phone_number LIKE '%3.5%';

-- DBS1.7.1.a::88::
-- Vor- und Nachname, Telefonnummer, Manager und E-Mail aller Mitarbeiter der Manager 147 oder 123, deren Telefonnummer mit 650 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, email
SELECT first_name, last_name, phone_number, manager_id, email
FROM hr.employees
WHERE manager_id IN (147, 123)
AND phone_number LIKE '650%';

-- DBS1.7.1.a::89::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 102 oder 146, deren Telefonnummer auf 268 endet.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (102, 146)
AND phone_number LIKE '%268';

-- DBS1.7.1.a::90::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 121 oder 148, deren Telefonnummer 50. enthält.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (121, 148)
AND phone_number LIKE '%50.%';

-- DBS1.7.1.a::91::
-- Vor- und Nachname, Telefonnummer, Manager und Gehalt aller Mitarbeiter der Manager 103 oder 147, deren Telefonnummer mit 011 anfängt.
-- Erwartete Spaltennamen: first_name, last_name, phone_number, manager_id, salary
SELECT first_name, last_name, phone_number, manager_id, salary
FROM hr.employees
WHERE manager_id IN (103, 147)
AND phone_number LIKE '011%';

-- DBS1.7.1.b::0::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf n endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen R und T ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%n'
AND SUBSTR(last_name,1,1) BETWEEN 'R' AND 'T';

-- DBS1.7.1.b::1::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf d endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen H und M ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%d'
AND SUBSTR(last_name,1,1) BETWEEN 'H' AND 'M';

-- DBS1.7.1.b::2::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf n endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen R und T ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%n'
AND SUBSTR(last_name,1,1) BETWEEN 'R' AND 'T';

-- DBS1.7.1.b::3::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf a endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen R und T ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%a'
AND SUBSTR(last_name,1,1) BETWEEN 'R' AND 'T';

-- DBS1.7.1.b::4::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf n endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen M und N ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%n'
AND SUBSTR(last_name,1,1) BETWEEN 'M' AND 'N';

-- DBS1.7.1.b::5::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf k endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen J und S ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%k'
AND SUBSTR(last_name,1,1) BETWEEN 'J' AND 'S';

-- DBS1.7.1.b::6::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf a endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen S und V ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%a'
AND SUBSTR(last_name,1,1) BETWEEN 'S' AND 'V';

-- DBS1.7.1.b::7::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf e endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen H und M ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%e'
AND SUBSTR(last_name,1,1) BETWEEN 'H' AND 'M';

-- DBS1.7.1.b::8::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf l endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen R und U ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%l'
AND SUBSTR(last_name,1,1) BETWEEN 'R' AND 'U';

-- DBS1.7.1.b::9::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf a endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen R und U ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%a'
AND SUBSTR(last_name,1,1) BETWEEN 'R' AND 'U';

-- DBS1.7.1.b::10::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf r endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen V und W ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%r'
AND SUBSTR(last_name,1,1) BETWEEN 'V' AND 'W';

-- DBS1.7.1.b::11::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf d endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen G und T ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%d'
AND SUBSTR(last_name,1,1) BETWEEN 'G' AND 'T';

-- DBS1.7.1.b::12::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf n endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen R und U ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%n'
AND SUBSTR(last_name,1,1) BETWEEN 'R' AND 'U';

-- DBS1.7.1.b::13::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf n endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen L und U ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%n'
AND SUBSTR(last_name,1,1) BETWEEN 'L' AND 'U';

-- DBS1.7.1.b::14::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf a endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen J und R ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%a'
AND SUBSTR(last_name,1,1) BETWEEN 'J' AND 'R';

-- DBS1.7.1.b::15::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf s endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen C und M ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%s'
AND SUBSTR(last_name,1,1) BETWEEN 'C' AND 'M';

-- DBS1.7.1.b::16::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf s endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen G und S ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%s'
AND SUBSTR(last_name,1,1) BETWEEN 'G' AND 'S';

-- DBS1.7.1.b::17::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf y endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen H und P ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%y'
AND SUBSTR(last_name,1,1) BETWEEN 'H' AND 'P';

-- DBS1.7.1.b::18::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf e endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen H und V ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%e'
AND SUBSTR(last_name,1,1) BETWEEN 'H' AND 'V';

-- DBS1.7.1.b::19::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf r endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen L und R ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%r'
AND SUBSTR(last_name,1,1) BETWEEN 'L' AND 'R';

-- DBS1.7.1.b::20::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf n endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen M und U ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%n'
AND SUBSTR(last_name,1,1) BETWEEN 'M' AND 'U';

-- DBS1.7.1.b::21::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf l endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen K und U ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%l'
AND SUBSTR(last_name,1,1) BETWEEN 'K' AND 'U';

-- DBS1.7.1.b::22::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf e endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen D und N ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%e'
AND SUBSTR(last_name,1,1) BETWEEN 'D' AND 'N';

-- DBS1.7.1.b::23::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf a endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen B und F ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%a'
AND SUBSTR(last_name,1,1) BETWEEN 'B' AND 'F';

-- DBS1.7.1.b::24::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf r endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen F und H ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%r'
AND SUBSTR(last_name,1,1) BETWEEN 'F' AND 'H';

-- DBS1.7.1.b::25::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf n endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen J und P ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%n'
AND SUBSTR(last_name,1,1) BETWEEN 'J' AND 'P';

-- DBS1.7.1.b::26::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf a endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen S und W ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%a'
AND SUBSTR(last_name,1,1) BETWEEN 'S' AND 'W';

-- DBS1.7.1.b::27::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf s endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen H und L ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%s'
AND SUBSTR(last_name,1,1) BETWEEN 'H' AND 'L';

-- DBS1.7.1.b::28::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf n endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen R und T ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%n'
AND SUBSTR(last_name,1,1) BETWEEN 'R' AND 'T';

-- DBS1.7.1.b::29::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf a endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen B und H ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%a'
AND SUBSTR(last_name,1,1) BETWEEN 'B' AND 'H';

-- DBS1.7.1.b::30::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf a endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen G und O ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%a'
AND SUBSTR(last_name,1,1) BETWEEN 'G' AND 'O';

-- DBS1.7.1.b::31::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf e endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen B und S ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%e'
AND SUBSTR(last_name,1,1) BETWEEN 'B' AND 'S';

-- DBS1.7.1.b::32::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf a endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen P und R ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%a'
AND SUBSTR(last_name,1,1) BETWEEN 'P' AND 'R';

-- DBS1.7.1.b::33::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf y endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen B und G ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%y'
AND SUBSTR(last_name,1,1) BETWEEN 'B' AND 'G';

-- DBS1.7.1.b::34::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf l endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen S und V ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%l'
AND SUBSTR(last_name,1,1) BETWEEN 'S' AND 'V';

-- DBS1.7.1.b::35::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf a endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen M und W ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%a'
AND SUBSTR(last_name,1,1) BETWEEN 'M' AND 'W';

-- DBS1.7.1.b::36::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf l endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen G und W ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%l'
AND SUBSTR(last_name,1,1) BETWEEN 'G' AND 'W';

-- DBS1.7.1.b::37::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf n endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen F und R ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%n'
AND SUBSTR(last_name,1,1) BETWEEN 'F' AND 'R';

-- DBS1.7.1.b::38::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf r endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen E und J ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%r'
AND SUBSTR(last_name,1,1) BETWEEN 'E' AND 'J';

-- DBS1.7.1.b::39::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf a endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen U und W ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%a'
AND SUBSTR(last_name,1,1) BETWEEN 'U' AND 'W';

-- DBS1.7.1.b::40::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf n endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen H und U ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%n'
AND SUBSTR(last_name,1,1) BETWEEN 'H' AND 'U';

-- DBS1.7.1.b::41::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf l endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen C und G ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%l'
AND SUBSTR(last_name,1,1) BETWEEN 'C' AND 'G';

-- DBS1.7.1.b::42::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf l endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen K und S ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%l'
AND SUBSTR(last_name,1,1) BETWEEN 'K' AND 'S';

-- DBS1.7.1.b::43::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf l endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen J und P ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%l'
AND SUBSTR(last_name,1,1) BETWEEN 'J' AND 'P';

-- DBS1.7.1.b::44::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf m endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen P und U ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%m'
AND SUBSTR(last_name,1,1) BETWEEN 'P' AND 'U';

-- DBS1.7.1.b::45::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf n endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen H und M ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%n'
AND SUBSTR(last_name,1,1) BETWEEN 'H' AND 'M';

-- DBS1.7.1.b::46::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf a endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen S und T ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%a'
AND SUBSTR(last_name,1,1) BETWEEN 'S' AND 'T';

-- DBS1.7.1.b::47::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf l endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen C und O ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%l'
AND SUBSTR(last_name,1,1) BETWEEN 'C' AND 'O';

-- DBS1.7.1.b::48::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf y endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen G und M ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%y'
AND SUBSTR(last_name,1,1) BETWEEN 'G' AND 'M';

-- DBS1.7.1.b::49::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf r endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen C und G ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%r'
AND SUBSTR(last_name,1,1) BETWEEN 'C' AND 'G';

-- DBS1.7.1.b::50::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf a endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen P und U ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%a'
AND SUBSTR(last_name,1,1) BETWEEN 'P' AND 'U';

-- DBS1.7.1.b::51::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf l endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen O und P ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%l'
AND SUBSTR(last_name,1,1) BETWEEN 'O' AND 'P';

-- DBS1.7.1.b::52::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf y endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen E und K ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%y'
AND SUBSTR(last_name,1,1) BETWEEN 'E' AND 'K';

-- DBS1.7.1.b::53::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf e endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen B und N ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%e'
AND SUBSTR(last_name,1,1) BETWEEN 'B' AND 'N';

-- DBS1.7.1.b::54::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf l endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen B und K ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%l'
AND SUBSTR(last_name,1,1) BETWEEN 'B' AND 'K';

-- DBS1.7.1.b::55::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf m endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen L und T ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%m'
AND SUBSTR(last_name,1,1) BETWEEN 'L' AND 'T';

-- DBS1.7.1.b::56::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf n endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen L und W ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%n'
AND SUBSTR(last_name,1,1) BETWEEN 'L' AND 'W';

-- DBS1.7.1.b::57::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf n endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen F und K ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%n'
AND SUBSTR(last_name,1,1) BETWEEN 'F' AND 'K';

-- DBS1.7.1.b::58::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf n endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen H und L ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%n'
AND SUBSTR(last_name,1,1) BETWEEN 'H' AND 'L';

-- DBS1.7.1.b::59::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf l endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen N und R ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%l'
AND SUBSTR(last_name,1,1) BETWEEN 'N' AND 'R';

-- DBS1.7.1.b::60::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf h endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen O und T ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%h'
AND SUBSTR(last_name,1,1) BETWEEN 'O' AND 'T';

-- DBS1.7.1.b::61::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf n endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen T und V ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%n'
AND SUBSTR(last_name,1,1) BETWEEN 'T' AND 'V';

-- DBS1.7.1.b::62::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf l endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen J und P ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%l'
AND SUBSTR(last_name,1,1) BETWEEN 'J' AND 'P';

-- DBS1.7.1.b::63::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf y endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen H und J ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%y'
AND SUBSTR(last_name,1,1) BETWEEN 'H' AND 'J';

-- DBS1.7.1.b::64::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf y endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen G und N ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%y'
AND SUBSTR(last_name,1,1) BETWEEN 'G' AND 'N';

-- DBS1.7.1.b::65::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf a endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen D und U ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%a'
AND SUBSTR(last_name,1,1) BETWEEN 'D' AND 'U';

-- DBS1.7.1.b::66::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf r endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen D und L ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%r'
AND SUBSTR(last_name,1,1) BETWEEN 'D' AND 'L';

-- DBS1.7.1.b::67::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf i endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen C und P ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%i'
AND SUBSTR(last_name,1,1) BETWEEN 'C' AND 'P';

-- DBS1.7.1.b::68::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf w endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen V und W ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%w'
AND SUBSTR(last_name,1,1) BETWEEN 'V' AND 'W';

-- DBS1.7.1.b::69::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf a endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen L und M ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%a'
AND SUBSTR(last_name,1,1) BETWEEN 'L' AND 'M';

-- DBS1.7.1.b::70::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf y endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen H und R ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%y'
AND SUBSTR(last_name,1,1) BETWEEN 'H' AND 'R';

-- DBS1.7.1.b::71::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf y endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen L und U ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%y'
AND SUBSTR(last_name,1,1) BETWEEN 'L' AND 'U';

-- DBS1.7.1.b::72::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf l endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen O und W ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%l'
AND SUBSTR(last_name,1,1) BETWEEN 'O' AND 'W';

-- DBS1.7.1.b::73::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf n endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen L und P ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%n'
AND SUBSTR(last_name,1,1) BETWEEN 'L' AND 'P';

-- DBS1.7.1.b::74::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf r endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen R und V ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%r'
AND SUBSTR(last_name,1,1) BETWEEN 'R' AND 'V';

-- DBS1.7.1.b::75::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf r endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen S und W ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%r'
AND SUBSTR(last_name,1,1) BETWEEN 'S' AND 'W';

-- DBS1.7.1.b::76::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf n endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen L und P ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%n'
AND SUBSTR(last_name,1,1) BETWEEN 'L' AND 'P';

-- DBS1.7.1.b::77::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf s endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen P und R ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%s'
AND SUBSTR(last_name,1,1) BETWEEN 'P' AND 'R';

-- DBS1.7.1.b::78::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf n endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen F und P ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%n'
AND SUBSTR(last_name,1,1) BETWEEN 'F' AND 'P';

-- DBS1.7.1.b::79::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf e endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen F und N ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%e'
AND SUBSTR(last_name,1,1) BETWEEN 'F' AND 'N';

-- DBS1.7.1.b::80::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf w endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen C und W ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%w'
AND SUBSTR(last_name,1,1) BETWEEN 'C' AND 'W';

-- DBS1.7.1.b::81::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf i endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen E und J ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%i'
AND SUBSTR(last_name,1,1) BETWEEN 'E' AND 'J';

-- DBS1.7.1.b::82::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf n endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen N und R ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%n'
AND SUBSTR(last_name,1,1) BETWEEN 'N' AND 'R';

-- DBS1.7.1.b::83::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf l endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen E und H ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%l'
AND SUBSTR(last_name,1,1) BETWEEN 'E' AND 'H';

-- DBS1.7.1.b::84::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf l endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen C und U ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%l'
AND SUBSTR(last_name,1,1) BETWEEN 'C' AND 'U';

-- DBS1.7.1.b::85::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf l endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen D und P ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%l'
AND SUBSTR(last_name,1,1) BETWEEN 'D' AND 'P';

-- DBS1.7.1.b::86::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf n endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen D und W ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%n'
AND SUBSTR(last_name,1,1) BETWEEN 'D' AND 'W';

-- DBS1.7.1.b::87::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf a endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen K und N ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%a'
AND SUBSTR(last_name,1,1) BETWEEN 'K' AND 'N';

-- DBS1.7.1.b::88::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf s endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen H und W ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%s'
AND SUBSTR(last_name,1,1) BETWEEN 'H' AND 'W';

-- DBS1.7.1.b::89::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf r endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen F und L ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%r'
AND SUBSTR(last_name,1,1) BETWEEN 'F' AND 'L';

-- DBS1.7.1.b::90::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf h endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen R und U ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%h'
AND SUBSTR(last_name,1,1) BETWEEN 'R' AND 'U';

-- DBS1.7.1.b::91::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf n endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen E und F ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%n'
AND SUBSTR(last_name,1,1) BETWEEN 'E' AND 'F';

-- DBS1.7.1.b::92::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf n endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen J und L ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%n'
AND SUBSTR(last_name,1,1) BETWEEN 'J' AND 'L';

-- DBS1.7.1.b::93::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf n endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen N und R ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%n'
AND SUBSTR(last_name,1,1) BETWEEN 'N' AND 'R';

-- DBS1.7.1.b::94::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf e endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen C und J ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%e'
AND SUBSTR(last_name,1,1) BETWEEN 'C' AND 'J';

-- DBS1.7.1.b::95::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf l endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen O und V ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%l'
AND SUBSTR(last_name,1,1) BETWEEN 'O' AND 'V';

-- DBS1.7.1.b::96::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf n endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen B und U ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%n'
AND SUBSTR(last_name,1,1) BETWEEN 'B' AND 'U';

-- DBS1.7.1.b::97::
-- Vor- und Nachname und Telefonnummer aller Mitarbeiter, deren Vorname auf a endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen P und U ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, phone_number
SELECT first_name, last_name, phone_number
FROM hr.employees
WHERE first_name LIKE '%a'
AND SUBSTR(last_name,1,1) BETWEEN 'P' AND 'U';

-- DBS1.7.1.b::98::
-- Vor- und Nachname und E-Mail aller Mitarbeiter, deren Vorname auf a endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen H und J ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, email
SELECT first_name, last_name, email
FROM hr.employees
WHERE first_name LIKE '%a'
AND SUBSTR(last_name,1,1) BETWEEN 'H' AND 'J';

-- DBS1.7.1.b::99::
-- Vor- und Nachname und Gehalt aller Mitarbeiter, deren Vorname auf s endet und bei denen der Anfangsbuchstabe des Nachnamens zwischen O und R ist (inklusive).
-- Erwartete Spaltennamen: first_name, last_name, salary
SELECT first_name, last_name, salary
FROM hr.employees
WHERE first_name LIKE '%s'
AND SUBSTR(last_name,1,1) BETWEEN 'O' AND 'R';

-- DBS1.7.1.c::0::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision größer als 0.15000000596046448 ist, absteigend sortiert nach Manager, Department und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct > 0.15000000596046448
ORDER BY manager_id DESC, department_id DESC, salary DESC;

-- DBS1.7.1.c::1::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision kleiner als 0.30000001192092896 ist, absteigend sortiert nach Department, Gehalt und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct < 0.30000001192092896
ORDER BY department_id DESC, salary DESC, manager_id DESC;

-- DBS1.7.1.c::2::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision größer als 0.10000000149011612 ist, sortiert nach Gehalt, Manager und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct > 0.10000000149011612
ORDER BY salary ASC, manager_id ASC, department_id ASC;

-- DBS1.7.1.c::3::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision größer als 0.20000000298023224 ist, sortiert nach Manager, Gehalt und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct > 0.20000000298023224
ORDER BY manager_id ASC, salary ASC, department_id ASC;

-- DBS1.7.1.c::4::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision größer als 0.15000000596046448 ist, absteigend sortiert nach Manager, Gehalt und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct > 0.15000000596046448
ORDER BY manager_id DESC, salary DESC, department_id DESC;

-- DBS1.7.1.c::5::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision kleiner als 0.30000001192092896 ist, sortiert nach Manager, Department und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct < 0.30000001192092896
ORDER BY manager_id ASC, department_id ASC, salary ASC;

-- DBS1.7.1.c::6::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision kleiner als 0.25 ist, absteigend sortiert nach Department, Manager und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct < 0.25
ORDER BY department_id DESC, manager_id DESC, salary DESC;

-- DBS1.7.1.c::7::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision größer als 0.20000000298023224 ist, sortiert nach Department, Manager und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct > 0.20000000298023224
ORDER BY department_id ASC, manager_id ASC, salary ASC;

-- DBS1.7.1.c::8::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision kleiner als 0.15000000596046448 ist, sortiert nach Manager, Gehalt und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct < 0.15000000596046448
ORDER BY manager_id ASC, salary ASC, department_id ASC;

-- DBS1.7.1.c::9::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision kleiner als 0.15000000596046448 ist, absteigend sortiert nach Gehalt, Department und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct < 0.15000000596046448
ORDER BY salary DESC, department_id DESC, manager_id DESC;

-- DBS1.7.1.c::10::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision größer als 0.20000000298023224 ist, absteigend sortiert nach Department, Manager und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct > 0.20000000298023224
ORDER BY department_id DESC, manager_id DESC, salary DESC;

-- DBS1.7.1.c::11::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision kleiner als 0.25 ist, sortiert nach Department, Manager und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct < 0.25
ORDER BY department_id ASC, manager_id ASC, salary ASC;

-- DBS1.7.1.c::12::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision größer als 0.30000001192092896 ist, sortiert nach Department, Manager und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct > 0.30000001192092896
ORDER BY department_id ASC, manager_id ASC, salary ASC;

-- DBS1.7.1.c::13::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision kleiner als 0.3499999940395355 ist, sortiert nach Manager, Department und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct < 0.3499999940395355
ORDER BY manager_id ASC, department_id ASC, salary ASC;

-- DBS1.7.1.c::14::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision größer als 0.10000000149011612 ist, sortiert nach Gehalt, Manager und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct > 0.10000000149011612
ORDER BY salary ASC, manager_id ASC, department_id ASC;

-- DBS1.7.1.c::15::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision kleiner als 0.20000000298023224 ist, sortiert nach Gehalt, Manager und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct < 0.20000000298023224
ORDER BY salary ASC, manager_id ASC, department_id ASC;

-- DBS1.7.1.c::16::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision größer als 0.25 ist, absteigend sortiert nach Department, Manager und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct > 0.25
ORDER BY department_id DESC, manager_id DESC, salary DESC;

-- DBS1.7.1.c::17::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision größer als 0.15000000596046448 ist, absteigend sortiert nach Gehalt, Department und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct > 0.15000000596046448
ORDER BY salary DESC, department_id DESC, manager_id DESC;

-- DBS1.7.1.c::18::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision kleiner als 0.3499999940395355 ist, absteigend sortiert nach Manager, Gehalt und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct < 0.3499999940395355
ORDER BY manager_id DESC, salary DESC, department_id DESC;

-- DBS1.7.1.c::19::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision größer als 0.30000001192092896 ist, absteigend sortiert nach Department, Gehalt und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct > 0.30000001192092896
ORDER BY department_id DESC, salary DESC, manager_id DESC;

-- DBS1.7.1.c::20::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision kleiner als 0.4000000059604645 ist, absteigend sortiert nach Manager, Gehalt und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct < 0.4000000059604645
ORDER BY manager_id DESC, salary DESC, department_id DESC;

-- DBS1.7.1.c::21::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision kleiner als 0.15000000596046448 ist, absteigend sortiert nach Department, Manager und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct < 0.15000000596046448
ORDER BY department_id DESC, manager_id DESC, salary DESC;

-- DBS1.7.1.c::22::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision größer als 0.20000000298023224 ist, sortiert nach Department, Manager und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct > 0.20000000298023224
ORDER BY department_id ASC, manager_id ASC, salary ASC;

-- DBS1.7.1.c::23::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision größer als 0.30000001192092896 ist, absteigend sortiert nach Department, Manager und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct > 0.30000001192092896
ORDER BY department_id DESC, manager_id DESC, salary DESC;

-- DBS1.7.1.c::24::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision kleiner als 0.3499999940395355 ist, absteigend sortiert nach Gehalt, Department und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct < 0.3499999940395355
ORDER BY salary DESC, department_id DESC, manager_id DESC;

-- DBS1.7.1.c::25::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision kleiner als 0.4000000059604645 ist, sortiert nach Manager, Department und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct < 0.4000000059604645
ORDER BY manager_id ASC, department_id ASC, salary ASC;

-- DBS1.7.1.c::26::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision größer als 0.10000000149011612 ist, sortiert nach Gehalt, Department und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct > 0.10000000149011612
ORDER BY salary ASC, department_id ASC, manager_id ASC;

-- DBS1.7.1.c::27::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision kleiner als 0.3499999940395355 ist, absteigend sortiert nach Manager, Department und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct < 0.3499999940395355
ORDER BY manager_id DESC, department_id DESC, salary DESC;

-- DBS1.7.1.c::28::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision kleiner als 0.25 ist, absteigend sortiert nach Department, Gehalt und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct < 0.25
ORDER BY department_id DESC, salary DESC, manager_id DESC;

-- DBS1.7.1.c::29::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision kleiner als 0.15000000596046448 ist, absteigend sortiert nach Manager, Department und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct < 0.15000000596046448
ORDER BY manager_id DESC, department_id DESC, salary DESC;

-- DBS1.7.1.c::30::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision größer als 0.20000000298023224 ist, sortiert nach Manager, Department und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct > 0.20000000298023224
ORDER BY manager_id ASC, department_id ASC, salary ASC;

-- DBS1.7.1.c::31::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision größer als 0.15000000596046448 ist, sortiert nach Manager, Department und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct > 0.15000000596046448
ORDER BY manager_id ASC, department_id ASC, salary ASC;

-- DBS1.7.1.c::32::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision kleiner als 0.3499999940395355 ist, sortiert nach Manager, Gehalt und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct < 0.3499999940395355
ORDER BY manager_id ASC, salary ASC, department_id ASC;

-- DBS1.7.1.c::33::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision kleiner als 0.15000000596046448 ist, absteigend sortiert nach Manager, Department und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct < 0.15000000596046448
ORDER BY manager_id DESC, department_id DESC, salary DESC;

-- DBS1.7.1.c::34::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision größer als 0.30000001192092896 ist, absteigend sortiert nach Department, Gehalt und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct > 0.30000001192092896
ORDER BY department_id DESC, salary DESC, manager_id DESC;

-- DBS1.7.1.c::35::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision größer als 0.25 ist, sortiert nach Department, Manager und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct > 0.25
ORDER BY department_id ASC, manager_id ASC, salary ASC;

-- DBS1.7.1.c::36::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision größer als 0.30000001192092896 ist, sortiert nach Manager, Gehalt und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct > 0.30000001192092896
ORDER BY manager_id ASC, salary ASC, department_id ASC;

-- DBS1.7.1.c::37::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision größer als 0.20000000298023224 ist, absteigend sortiert nach Department, Gehalt und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct > 0.20000000298023224
ORDER BY department_id DESC, salary DESC, manager_id DESC;

-- DBS1.7.1.c::38::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision kleiner als 0.15000000596046448 ist, sortiert nach Manager, Department und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct < 0.15000000596046448
ORDER BY manager_id ASC, department_id ASC, salary ASC;

-- DBS1.7.1.c::39::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision größer als 0.25 ist, absteigend sortiert nach Manager, Department und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct > 0.25
ORDER BY manager_id DESC, department_id DESC, salary DESC;

-- DBS1.7.1.c::40::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision kleiner als 0.30000001192092896 ist, sortiert nach Department, Gehalt und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct < 0.30000001192092896
ORDER BY department_id ASC, salary ASC, manager_id ASC;

-- DBS1.7.1.c::41::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision größer als 0.15000000596046448 ist, sortiert nach Gehalt, Department und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct > 0.15000000596046448
ORDER BY salary ASC, department_id ASC, manager_id ASC;

-- DBS1.7.1.c::42::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision größer als 0.10000000149011612 ist, absteigend sortiert nach Manager, Gehalt und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct > 0.10000000149011612
ORDER BY manager_id DESC, salary DESC, department_id DESC;

-- DBS1.7.1.c::43::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision kleiner als 0.20000000298023224 ist, sortiert nach Manager, Gehalt und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct < 0.20000000298023224
ORDER BY manager_id ASC, salary ASC, department_id ASC;

-- DBS1.7.1.c::44::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision größer als 0.20000000298023224 ist, sortiert nach Gehalt, Department und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct > 0.20000000298023224
ORDER BY salary ASC, department_id ASC, manager_id ASC;

-- DBS1.7.1.c::45::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision kleiner als 0.4000000059604645 ist, absteigend sortiert nach Department, Gehalt und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct < 0.4000000059604645
ORDER BY department_id DESC, salary DESC, manager_id DESC;

-- DBS1.7.1.c::46::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision größer als 0.25 ist, absteigend sortiert nach Department, Gehalt und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct > 0.25
ORDER BY department_id DESC, salary DESC, manager_id DESC;

-- DBS1.7.1.c::47::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision größer als 0.20000000298023224 ist, sortiert nach Gehalt, Department und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct > 0.20000000298023224
ORDER BY salary ASC, department_id ASC, manager_id ASC;

-- DBS1.7.1.c::48::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision kleiner als 0.4000000059604645 ist, absteigend sortiert nach Gehalt, Manager und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct < 0.4000000059604645
ORDER BY salary DESC, manager_id DESC, department_id DESC;

-- DBS1.7.1.c::49::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision kleiner als 0.15000000596046448 ist, absteigend sortiert nach Manager, Gehalt und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct < 0.15000000596046448
ORDER BY manager_id DESC, salary DESC, department_id DESC;

-- DBS1.7.1.c::50::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision kleiner als 0.4000000059604645 ist, absteigend sortiert nach Manager, Department und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct < 0.4000000059604645
ORDER BY manager_id DESC, department_id DESC, salary DESC;

-- DBS1.7.1.c::51::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision kleiner als 0.30000001192092896 ist, absteigend sortiert nach Gehalt, Manager und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct < 0.30000001192092896
ORDER BY salary DESC, manager_id DESC, department_id DESC;

-- DBS1.7.1.c::52::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision größer als 0.15000000596046448 ist, absteigend sortiert nach Gehalt, Manager und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct > 0.15000000596046448
ORDER BY salary DESC, manager_id DESC, department_id DESC;

-- DBS1.7.1.c::53::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision kleiner als 0.30000001192092896 ist, absteigend sortiert nach Manager, Department und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct < 0.30000001192092896
ORDER BY manager_id DESC, department_id DESC, salary DESC;

-- DBS1.7.1.c::54::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision größer als 0.30000001192092896 ist, sortiert nach Department, Manager und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct > 0.30000001192092896
ORDER BY department_id ASC, manager_id ASC, salary ASC;

-- DBS1.7.1.c::55::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision kleiner als 0.15000000596046448 ist, sortiert nach Gehalt, Manager und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct < 0.15000000596046448
ORDER BY salary ASC, manager_id ASC, department_id ASC;

-- DBS1.7.1.c::56::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision kleiner als 0.4000000059604645 ist, sortiert nach Manager, Gehalt und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct < 0.4000000059604645
ORDER BY manager_id ASC, salary ASC, department_id ASC;

-- DBS1.7.1.c::57::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision größer als 0.30000001192092896 ist, sortiert nach Manager, Department und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct > 0.30000001192092896
ORDER BY manager_id ASC, department_id ASC, salary ASC;

-- DBS1.7.1.c::58::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision kleiner als 0.15000000596046448 ist, sortiert nach Department, Manager und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct < 0.15000000596046448
ORDER BY department_id ASC, manager_id ASC, salary ASC;

-- DBS1.7.1.c::59::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision größer als 0.15000000596046448 ist, absteigend sortiert nach Department, Manager und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct > 0.15000000596046448
ORDER BY department_id DESC, manager_id DESC, salary DESC;

-- DBS1.7.1.c::60::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision kleiner als 0.20000000298023224 ist, absteigend sortiert nach Department, Gehalt und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct < 0.20000000298023224
ORDER BY department_id DESC, salary DESC, manager_id DESC;

-- DBS1.7.1.c::61::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision größer als 0.15000000596046448 ist, absteigend sortiert nach Department, Gehalt und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct > 0.15000000596046448
ORDER BY department_id DESC, salary DESC, manager_id DESC;

-- DBS1.7.1.c::62::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision größer als 0.15000000596046448 ist, sortiert nach Department, Gehalt und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct > 0.15000000596046448
ORDER BY department_id ASC, salary ASC, manager_id ASC;

-- DBS1.7.1.c::63::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision kleiner als 0.3499999940395355 ist, sortiert nach Department, Gehalt und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct < 0.3499999940395355
ORDER BY department_id ASC, salary ASC, manager_id ASC;

-- DBS1.7.1.c::64::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision größer als 0.25 ist, absteigend sortiert nach Manager, Department und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct > 0.25
ORDER BY manager_id DESC, department_id DESC, salary DESC;

-- DBS1.7.1.c::65::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision größer als 0.30000001192092896 ist, absteigend sortiert nach Manager, Gehalt und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct > 0.30000001192092896
ORDER BY manager_id DESC, salary DESC, department_id DESC;

-- DBS1.7.1.c::66::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision kleiner als 0.3499999940395355 ist, absteigend sortiert nach Manager, Gehalt und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct < 0.3499999940395355
ORDER BY manager_id DESC, salary DESC, department_id DESC;

-- DBS1.7.1.c::67::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision größer als 0.25 ist, sortiert nach Manager, Department und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct > 0.25
ORDER BY manager_id ASC, department_id ASC, salary ASC;

-- DBS1.7.1.c::68::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision größer als 0.15000000596046448 ist, absteigend sortiert nach Department, Manager und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct > 0.15000000596046448
ORDER BY department_id DESC, manager_id DESC, salary DESC;

-- DBS1.7.1.c::69::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision größer als 0.20000000298023224 ist, sortiert nach Manager, Department und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct > 0.20000000298023224
ORDER BY manager_id ASC, department_id ASC, salary ASC;

-- DBS1.7.1.c::70::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision größer als 0.30000001192092896 ist, sortiert nach Manager, Department und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct > 0.30000001192092896
ORDER BY manager_id ASC, department_id ASC, salary ASC;

-- DBS1.7.1.c::71::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision kleiner als 0.15000000596046448 ist, absteigend sortiert nach Department, Gehalt und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct < 0.15000000596046448
ORDER BY department_id DESC, salary DESC, manager_id DESC;

-- DBS1.7.1.c::72::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision kleiner als 0.30000001192092896 ist, absteigend sortiert nach Manager, Gehalt und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct < 0.30000001192092896
ORDER BY manager_id DESC, salary DESC, department_id DESC;

-- DBS1.7.1.c::73::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision kleiner als 0.20000000298023224 ist, absteigend sortiert nach Manager, Department und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct < 0.20000000298023224
ORDER BY manager_id DESC, department_id DESC, salary DESC;

-- DBS1.7.1.c::74::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision kleiner als 0.30000001192092896 ist, absteigend sortiert nach Gehalt, Department und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct < 0.30000001192092896
ORDER BY salary DESC, department_id DESC, manager_id DESC;

-- DBS1.7.1.c::75::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision kleiner als 0.3499999940395355 ist, absteigend sortiert nach Department, Manager und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct < 0.3499999940395355
ORDER BY department_id DESC, manager_id DESC, salary DESC;

-- DBS1.7.1.c::76::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision größer als 0.10000000149011612 ist, absteigend sortiert nach Gehalt, Department und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct > 0.10000000149011612
ORDER BY salary DESC, department_id DESC, manager_id DESC;

-- DBS1.7.1.c::77::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision größer als 0.30000001192092896 ist, absteigend sortiert nach Gehalt, Manager und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct > 0.30000001192092896
ORDER BY salary DESC, manager_id DESC, department_id DESC;

-- DBS1.7.1.c::78::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision größer als 0.30000001192092896 ist, sortiert nach Department, Gehalt und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct > 0.30000001192092896
ORDER BY department_id ASC, salary ASC, manager_id ASC;

-- DBS1.7.1.c::79::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision kleiner als 0.20000000298023224 ist, absteigend sortiert nach Gehalt, Department und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct < 0.20000000298023224
ORDER BY salary DESC, department_id DESC, manager_id DESC;

-- DBS1.7.1.c::80::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision kleiner als 0.30000001192092896 ist, absteigend sortiert nach Gehalt, Department und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct < 0.30000001192092896
ORDER BY salary DESC, department_id DESC, manager_id DESC;

-- DBS1.7.1.c::81::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision kleiner als 0.20000000298023224 ist, absteigend sortiert nach Manager, Gehalt und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct < 0.20000000298023224
ORDER BY manager_id DESC, salary DESC, department_id DESC;

-- DBS1.7.1.c::82::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision kleiner als 0.15000000596046448 ist, sortiert nach Department, Manager und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct < 0.15000000596046448
ORDER BY department_id ASC, manager_id ASC, salary ASC;

-- DBS1.7.1.c::83::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision kleiner als 0.15000000596046448 ist, absteigend sortiert nach Department, Gehalt und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct < 0.15000000596046448
ORDER BY department_id DESC, salary DESC, manager_id DESC;

-- DBS1.7.1.c::84::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision kleiner als 0.3499999940395355 ist, absteigend sortiert nach Department, Gehalt und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct < 0.3499999940395355
ORDER BY department_id DESC, salary DESC, manager_id DESC;

-- DBS1.7.1.c::85::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision kleiner als 0.30000001192092896 ist, absteigend sortiert nach Manager, Gehalt und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct < 0.30000001192092896
ORDER BY manager_id DESC, salary DESC, department_id DESC;

-- DBS1.7.1.c::86::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision größer als 0.20000000298023224 ist, absteigend sortiert nach Department, Gehalt und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct > 0.20000000298023224
ORDER BY department_id DESC, salary DESC, manager_id DESC;

-- DBS1.7.1.c::87::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision kleiner als 0.20000000298023224 ist, absteigend sortiert nach Gehalt, Department und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct < 0.20000000298023224
ORDER BY salary DESC, department_id DESC, manager_id DESC;

-- DBS1.7.1.c::88::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision kleiner als 0.3499999940395355 ist, sortiert nach Department, Manager und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct < 0.3499999940395355
ORDER BY department_id ASC, manager_id ASC, salary ASC;

-- DBS1.7.1.c::89::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision kleiner als 0.20000000298023224 ist, sortiert nach Gehalt, Manager und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct < 0.20000000298023224
ORDER BY salary ASC, manager_id ASC, department_id ASC;

-- DBS1.7.1.c::90::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision kleiner als 0.20000000298023224 ist, absteigend sortiert nach Manager, Gehalt und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct < 0.20000000298023224
ORDER BY manager_id DESC, salary DESC, department_id DESC;

-- DBS1.7.1.c::91::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision größer als 0.20000000298023224 ist, sortiert nach Gehalt, Department und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct > 0.20000000298023224
ORDER BY salary ASC, department_id ASC, manager_id ASC;

-- DBS1.7.1.c::92::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision kleiner als 0.20000000298023224 ist, sortiert nach Department, Manager und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct < 0.20000000298023224
ORDER BY department_id ASC, manager_id ASC, salary ASC;

-- DBS1.7.1.c::93::
-- Vor- und Nachname, Department, Manager und Telefonnummer aller Mitarbeiter, deren Provision kleiner als 0.30000001192092896 ist, sortiert nach Manager, Gehalt und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, phone_number
SELECT first_name, last_name, department_id, manager_id, phone_number
FROM hr.employees
WHERE commission_pct < 0.30000001192092896
ORDER BY manager_id ASC, salary ASC, department_id ASC;

-- DBS1.7.1.c::94::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision größer als 0.25 ist, absteigend sortiert nach Manager, Department und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct > 0.25
ORDER BY manager_id DESC, department_id DESC, salary DESC;

-- DBS1.7.1.c::95::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision kleiner als 0.20000000298023224 ist, absteigend sortiert nach Gehalt, Department und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct < 0.20000000298023224
ORDER BY salary DESC, department_id DESC, manager_id DESC;

-- DBS1.7.1.c::96::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision kleiner als 0.20000000298023224 ist, absteigend sortiert nach Manager, Gehalt und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct < 0.20000000298023224
ORDER BY manager_id DESC, salary DESC, department_id DESC;

-- DBS1.7.1.c::97::
-- Vor- und Nachname, Department, Manager und Gehalt aller Mitarbeiter, deren Provision größer als 0.20000000298023224 ist, sortiert nach Department, Gehalt und Manager.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, salary
SELECT first_name, last_name, department_id, manager_id, salary
FROM hr.employees
WHERE commission_pct > 0.20000000298023224
ORDER BY department_id ASC, salary ASC, manager_id ASC;

-- DBS1.7.1.c::98::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision größer als 0.30000001192092896 ist, sortiert nach Manager, Department und Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct > 0.30000001192092896
ORDER BY manager_id ASC, department_id ASC, salary ASC;

-- DBS1.7.1.c::99::
-- Vor- und Nachname, Department, Manager und E-Mail aller Mitarbeiter, deren Provision größer als 0.25 ist, absteigend sortiert nach Manager, Gehalt und Department.
-- Erwartete Spaltennamen: first_name, last_name, department_id, manager_id, email
SELECT first_name, last_name, department_id, manager_id, email
FROM hr.employees
WHERE commission_pct > 0.25
ORDER BY manager_id DESC, salary DESC, department_id DESC;

-- DBS1.7.1.d::0::
-- Vor- und Nachname, Gehalt und E-Mail aller Mitarbeiter, die keinem Manager zugeordnet sind oder die mehr als 10000 verdienen, sortiert nach Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, salary, email
SELECT first_name, last_name, salary, email
FROM hr.employees
WHERE manager_id IS NULL
OR salary > 10000
ORDER BY salary ASC;

-- DBS1.7.1.d::1::
-- Vor- und Nachname, Gehalt und E-Mail aller Mitarbeiter, die keinem Manager zugeordnet sind oder die mehr als 10000 verdienen, absteigend sortiert nach Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, salary, email
SELECT first_name, last_name, salary, email
FROM hr.employees
WHERE manager_id IS NULL
OR salary > 10000
ORDER BY salary DESC;

-- DBS1.7.1.d::2::
-- Vor- und Nachname, Gehalt und Telefonnummer aller Mitarbeiter, die keinem Manager zugeordnet sind oder die mehr als 10000 verdienen, sortiert nach Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, salary, phone_number
SELECT first_name, last_name, salary, phone_number
FROM hr.employees
WHERE manager_id IS NULL
OR salary > 10000
ORDER BY salary ASC;

-- DBS1.7.1.d::3::
-- Vor- und Nachname, Gehalt und Telefonnummer aller Mitarbeiter, die keinem Manager zugeordnet sind oder die mehr als 10000 verdienen, absteigend sortiert nach Gehalt.
-- Erwartete Spaltennamen: first_name, last_name, salary, phone_number
SELECT first_name, last_name, salary, phone_number
FROM hr.employees
WHERE manager_id IS NULL
OR salary > 10000
ORDER BY salary DESC;

