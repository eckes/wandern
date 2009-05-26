-- phpMyAdmin SQL Dump
-- version 3.1.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 26. Mai 2009 um 22:15
-- Server Version: 5.1.30
-- PHP-Version: 5.2.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `wandern`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `walks`
--

CREATE TABLE IF NOT EXISTS `walks` (
  `Tag` varchar(10) NOT NULL,
  `Name` varchar(200) NOT NULL,
  `Laenge` float(3,1) NOT NULL,
  `Dauer` float(3,1) NOT NULL,
  `Charakter` varchar(200) NOT NULL,
  `Lat` float(10,6) NOT NULL,
  `Lon` float(10,6) NOT NULL,
  `Datum` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `walks`
--

INSERT INTO `walks` (`Tag`, `Name`, `Laenge`, `Dauer`, `Charakter`, `Lat`, `Lon`, `Datum`) VALUES
('MLUW2_01', 'Aussichtsreicher Streifzug durch den Süden des Walberla', 23.0, 6.0, 'leichtes bis mittelschweres Gelände', 11.187429, 49.668682, '0000-00-00'),
('MLUW2_15', 'Auf dem Hubertussteig durch den Veldensteiner Forst', 20.5, 5.5, 'überwiegend leichtes Gelände', 11.494339, 49.698593, '0000-00-00'),
('MLUW1_01', 'Geruhsame Waldwanderung durch die romantische Bitterbachschlucht', 11.0, 3.5, 'leichtes Gelände', 11.264956, 49.509521, '2006-09-25'),
('MLUW1_02', 'Zum Aussichts- und Kletterfelsen Glatzenstein', 12.0, 3.5, 'überwiegend leichtes Gelände, Anstieg zum Glatzenstein', 11.378746, 49.530472, '0000-00-00'),
('MLUW1_03', 'Abwechslungsreicher Weg von Hersbruck nach Eschenbach', 11.5, 4.0, 'hügeliges Gelände', 11.432036, 49.515312, '0000-00-00'),
('MLUW1_04', 'Von Kleedorf über die Felsnadel Langenstein und das altfränkische Pfarrdorf Vorra', 14.0, 5.0, 'leichtes bis mittelschweres Gelände', 11.444610, 49.536396, '2006-09-12'),
('MLUW1_05', 'Zum Aussichtsberg Zankelstein und ins Aichatal', 12.0, 4.0, 'leichtes, mitunter ansteigendes Gelände', 11.513565, 49.505356, '2006-03-04'),
('MLUW1_06', 'Von Hubmersberg zu den Aussichtspunkten Eschenbacher Geißkirche und Lochfels', 10.0, 3.5, 'hügeliges, mitunter steiles Gelände', 11.507084, 49.527103, '0000-00-00'),
('MLUW1_07', 'Auf unbekannten Pfaden von Oed über die Noris-Schanze und den Knappenberg nach Neukirchen, Ermhof und Ernhüll', 13.0, 4.0, 'hügeliges, teilweise anstrengendes Gelände', 11.570063, 49.508018, '2007-06-10'),
('MLUW1_08', 'Felsgestein um Etzelwand und Kirchenreinbach, "Windloch" von Buchhof und Schloss Neidstein', 12.0, 4.0, 'hügeliges Gelände', 11.584477, 49.526363, '2006-10-29'),
('MLUW1_09', 'Höhlenweg ab Neutras', 20.0, 5.5, 'hügeliges, zwischendurch anstrengendes Gelände', 11.545258, 49.533676, '0000-00-00'),
('MLUW1_10', 'Von Hirschbach aus ins Klettergebiet "Höhenglücksteig"', 9.0, 3.0, 'hügeliges Gelände mit einem steilen Anstieg', 11.540816, 49.556343, '0000-00-00'),
('MLUW1_11', 'Zur Petershöhle zwischen Hartenstein und Velden', 14.0, 4.5, 'hügeliges Gelände', 11.480863, 49.597881, '2008-02-17'),
('MLUW1_12', 'Zum höchsten Punkt der Frankenalb, der Burgruine Hohenstein', 14.5, 4.5, 'überwiegend leichtes, zwischendurch anstrengendes Gelände', 11.480863, 49.597881, '2006-11-25'),
('MLUW1_13', 'Höhen- und Tälerwanderung ab Velden', 14.5, 5.0, 'welliges, zwischendurch anstrengendes Gelände', 11.508608, 49.611668, '2009-03-14'),
('MLUW1_14', 'Exkursion durch die geheimnisvolle Welt der Grotten', 11.5, 3.5, 'hügeliges Gelände mit zwei steileren Anstiegen', 11.589332, 49.629463, '2007-06-03'),
('MLUW1_15', 'Bizarre Felsformationen nördlich der Maximiliansgrotte', 16.0, 4.5, 'hügeliges, mitunter steiles Gelände', 11.589332, 49.629463, '0000-00-00'),
('MLUW1_16', 'Wanderung von Königstein im Oberpfälzer Jura zu den Kapellen in Breitenstein', 13.0, 4.5, 'hügeliges, mitunter steiles Gelände', 11.634007, 49.604507, '0000-00-00'),
('MLUW1_17', 'Von Weidensees durch das romantische Klumpertal nach Bronn', 13.5, 4.0, 'leichtes Gelände', 11.440566, 49.710426, '2009-05-01'),
('MLUW1_18', 'Durchs Püttlachtal zur Elbersberger Kapelle und zur Ruine Hollenberg', 12.0, 4.5, 'hügeliges Gelände', 11.498244, 49.774559, '0000-00-00'),
('MLUW1_19', 'Auf stillen Pfaden zur Kirche von St. Helena', 9.0, 3.0, 'überwiegend leichtes Gelände mit steileren An- und Abstiegen', 11.379733, 49.631771, '2007-01-01'),
('MLUW1_20', 'Zu den Burgruinen von Wildenfels und Stierberg', 16.0, 5.0, 'überwiegend leichtes, zwischendurch anstrengendes Gelände', 11.360893, 49.656239, '2007-04-01'),
('MLUW1_21', 'Von Markt Igensdorf zum Kloster Weißenohe und zur Lillingquelle', 15.0, 4.5, 'hügeliges Gelände mit einigen Anstiegen', 11.232018, 49.627335, '2006-08-31'),
('MLUW1_22', 'Durchs Felsengebiet "Hohle Kirche" ins Großenoher Tal', 10.0, 3.5, 'leichtes Gelände mit einem Anstieg', 11.283860, 49.654266, '2006-09-17'),
('MLUW1_23', 'Von Hiltpoltstein in Trubachtal und zur Burgruine Leienfels', 19.0, 5.5, 'hügeliges, mitunter steiles Gelände', 11.321754, 49.660378, '2006-06-10'),
('MLUW1_24', 'Zur Burgruine Bärnfels und ins Pitztal', 8.0, 3.0, 'leichtes Gelände', 11.338234, 49.715515, '2008-06-30'),
('MLUW1_25', 'Von Regenthal über die Kreuzkapelle oberhalb Pottensteins in den Landschaftsgarten Klumpertal', 16.5, 4.5, 'hügeliges, mitunter steiles Gelände', 11.398787, 49.731277, '0000-00-00'),
('MLUW1_26', 'Über Höhenwege ins Naturschutzgebiet Ehrenbürg', 12.0, 4.0, 'hügeliges Gelände', 11.146381, 49.730820, '0000-00-00'),
('MLUW1_27', 'Auf den Spuren des Leutenbacher Heimatdichters und Pfarrers Dr. Georg Kanzler', 18.0, 5.5, 'hügeliges, mitunter steiles Gelände', 11.172645, 49.710358, '2007-06-07'),
('MLUW1_28', 'Über die Höhenburg Dietrichstein ins Trubachtal', 19.0, 5.5, 'hügeliges, mitunter anstrengendes Gelände', 11.173782, 49.755096, '0000-00-00'),
('MLUW1_29', 'Aussichtsfelsen um Egloffstein', 10.0, 3.0, 'überwiegend leichtes, zwischendurch anstrengendes Gelände', 11.259270, 49.703388, '2008-09-27'),
('MLUW1_30', 'Von Morschreuth zum Rötelfels', 10.0, 3.5, 'leichtes, mitunter steiles Gelände', 11.263947, 49.754875, '2008-05-11'),
('MLUW1_31', 'Geruhsame Waldwanderung ab Ebermannstadt mit schönen Aussichten', 12.0, 3.5, 'überwiegend leichtes Gelände', 11.182108, 49.786667, '0000-00-00'),
('MLUW1_32', 'Burgruine Neideck und Traumaussicht vom Zuckerhut', 12.0, 3.5, 'leichtes bis mittelschweres Gelände', 11.221075, 49.806889, '0000-00-00'),
('MLUW1_33', 'Durchs Werntal zum sagenumwobenen Totenstein', 13.0, 4.5, 'hügeliges, mitunter anstrengendes Gelände', 11.197815, 49.840973, '2006-07-29'),
('MLUW1_34', 'Von Kalteneggolsfeld über den Altenberg nach Burggrub', 11.5, 3.5, 'hügeliges Gelände', 11.119108, 49.846329, '0000-00-00'),
('MLUW1_35', 'Von Burggrub zur Leinleiter-Quelle und dem größten Mühlrad der Fränkischen Schweiz', 11.0, 3.5, 'leichtes Gelände mit zwei Anstiegen', 11.137133, 49.875595, '0000-00-00'),
('MLUW1_36', 'Aussichtsreiche Höhen über dem Wiesenttal, die Oswaldhöhle und Blick vom Hohen Kreuz', 14.0, 4.0, 'hügeliges, mitunter anstrengendes Gelände', 11.217427, 49.808315, '2007-07-08'),
('MLUW1_37', 'Über den Rücken des Hummerbergs und zur Ruine Streitburg', 10.0, 3.5, 'überwiegend leichtes Gelände, am Anfang Anstieg', 11.217427, 49.808315, '2007-10-14'),
('MLUW1_38', 'Von Muggendorf zum Naturdenkmal Druidenhain bei Wohlmannsgesees', 11.0, 4.0, 'hügeliges, mitunter ansteigendes Gelände', 11.261523, 49.802929, '0000-00-00'),
('MLUW1_39', 'Durchs Aufseßtal über Muggendorf zum geheimnisvollen Labyrinth der Riesenburg', 13.5, 4.0, 'hügeliges Gelände mit steilen An- und Abstiegen', 11.296241, 49.811584, '2006-10-22'),
('MLUW1_40', 'Zu den Burgen Rabeneck und Rabenstein', 15.0, 4.5, 'hügeliges, mitunter anstrengendes Gelände', 11.296241, 49.811584, '0000-00-00'),
('MLUW1_41', 'Malerische Felsgebiete zwischen Oberailsfeld und Kirchahorn', 11.0, 3.5, 'teils hügeliges, teils leichtes Gelände', 11.353812, 49.812622, '0000-00-00'),
('MLUW1_42', 'Von Waischenfeld zum Zusammenfluss von Wiesent und Truppach', 16.0, 4.5, 'überwiegend leichtes Gelände', 11.344950, 49.854446, '2006-01-01'),
('MLUW1_43', 'Durchs Aufseßtal nach Siegritzberg', 12.0, 3.5, 'überwiegend leichtes Gelände mit zwei steileren Anstiegen', 11.251523, 49.851265, '0000-00-00'),
('MLUW1_44', 'Auf stillen Wegen zum Naturdenkmal "Russenlinde" und ins Aufseßtal', 14.0, 4.5, 'leichtes Gelände', 11.238756, 49.870853, '2008-08-01'),
('MLUW1_45', 'Von Plankenfels durchs Lochautal und über aussichtsreiche Höhen zurück', 18.0, 4.5, 'hügeliges, überwiegend leichtes Gelände', 11.334629, 49.883507, '2007-03-10'),
('MLUW1_46', 'Ins Mühlenviertel der Fränkischen Schweiz', 11.0, 3.5, 'hügeliges aber überwiegend leichtes Gelände', 11.334329, 49.780128, '0000-00-00'),
('MLUW1_47', 'Vom Wallfahrtsort Gößweinstein durchs Wiesenttal nach Burggaillenreuth', 12.5, 3.5, 'überwiegend leichtes Gelände mit je einem steilen Ab- und Anstieg', 11.340315, 49.767448, '0000-00-00'),
('MLUW1_48', 'Durchs Püttlachtal und zur Elbersberg-Kapelle', 11.5, 3.5, 'überwiegend leichtes Gelände mit einem steileren Anstieg', 11.409152, 49.768333, '0000-00-00'),
('MLUW1_49', 'Von Pottenstein ins Felsendorf Tüchersfeld und nach Gößweinstein', 14.0, 4.5, 'hügeliges, mitunter anstrengendes Gelände', 11.409152, 49.768333, '0000-00-00'),
('MLUW1_50', 'Zum Aussichtsturm auf der Hohenmirsberger Platte', 15.5, 5.0, 'hügeliges, mitunter anseigendes Gelände', 11.409152, 49.768333, '0000-00-00'),
('FUW1_45', 'Rund um den "Starenfels"', 8.8, 2.0, '', 11.567903, 49.547256, '0000-00-00'),
('FUW1_46', 'Durch die Hartensteiner Berge', 11.0, 2.5, '', 11.524428, 49.595165, '0000-00-00'),
('FUW1_47', 'Zur "Steinernen Stadt" und zur "Weisingkuppe"', 11.0, 2.5, '', 11.588400, 49.628502, '0000-00-00'),
('FUW1_48', 'Über Gnadenberg ins Traunfelder und Raschbacher Tal', 13.2, 3.0, '', 11.401914, 49.381538, '0000-00-00'),
('FUW1_49', 'Rund um den Ernhofer Berg', 17.6, 4.0, '', 11.353903, 49.388546, '0000-00-00'),
('FUW1_50', 'Auf die Sattelhöhen zwischen Nonnenberg, Buchenberg und Ernhofer Berg', 8.8, 2.0, '', 11.364592, 49.451115, '0000-00-00'),
('FUW1_51', 'Zum "Hohlen Felsen" auf der Houbirg', 8.8, 2.0, '', 11.471103, 49.493195, '0000-00-00'),
('FUW1_52', 'Rund um das "Düsselwöhr" bei Förrenbach', 6.6, 1.5, '', 11.502753, 49.475853, '0000-00-00'),
('FUW1_53', 'Zum stillen Molsberger Tal durch den Fichtengraben', 8.8, 2.0, '', 11.502753, 49.475853, '0000-00-00'),
('FUW1_54', 'Durchs Molsberger Tal zur Ruine Reicheneck', 11.0, 2.5, '', 11.502753, 49.475853, '0000-00-00'),
('FUW1_55', 'Rund um die "Wacht" bei Thalheim', 6.6, 1.5, '', 11.542064, 49.459019, '0000-00-00'),
('FUW1_56', 'Links und rechts des Förrenbachtals', 13.2, 3.0, '', 11.542064, 49.459019, '0000-00-00'),
('FUW1_57', 'Durchs Kirchtal zur Einöde Othenberg und zur Regelsmühle', 11.0, 2.5, '', 11.564108, 49.454285, '0000-00-00'),
('FUW1_58', 'Zum hochliegenden Trossalter', 11.0, 2.5, '', 11.564108, 49.454285, '0000-00-00'),
('FUW1_59', 'Durchs einsame Schottental', 8.8, 2.0, '', 11.561839, 49.453522, '0000-00-00'),
('FUW1_60', 'Über Wurmrausch zur Ruine Lichtenegg', 11.0, 2.5, '', 11.597597, 49.456909, '0000-00-00'),
('FUW1_44', 'Zur "Schlangenfichte" und durchs Reichental', 11.0, 2.5, '', 11.540842, 49.556328, '0000-00-00'),
('FUW1_01', 'Von Kraftshof in den westlichen Sebalder Reichswald', 11.0, 2.5, '', 11.046770, 49.512432, '0000-00-00'),
('FUW1_02', 'Über das "Frauenkreuz" zur Gründlach', 13.2, 3.0, '', 11.111405, 49.504059, '0000-00-00'),
('FUW1_03', 'Rings um den Haidberg bei Heroldsberg', 6.6, 1.5, '', 11.134692, 49.506210, '0000-00-00'),
('FUW1_04', 'Der Gründlach und dem Simmelbach entlang', 16.5, 4.0, '', 11.134686, 49.506207, '0000-00-00'),
('FUW1_05', 'Durch die Waldungen zwischen Heroldsberg und Haidberg', 13.2, 3.0, '', 11.134686, 49.506207, '0000-00-00'),
('FUW1_06', 'Ins Jungferntal, zu den Sambach- und Kreuzweihern', 11.0, 2.5, '', 11.190605, 49.594467, '0000-00-00'),
('FUW1_07', 'Von Eschenau zum Beerbacher Kirchlein und nach Neunhof', 15.4, 3.5, '', 11.132201, 49.557972, '0000-00-00'),
('FUW1_08', 'Ein Streifzug zur "Bärenmarter" und "Roten Marter"', 11.0, 2.5, '', 11.200203, 49.480221, '0000-00-00'),
('FUW1_09', 'Von Oedenberg aus in den Sebalder Wald', 11.0, 2.5, '', 11.205114, 49.532986, '0000-00-00'),
('FUW1_10', 'Zu alten Herrensitzen am Rande des Reichswaldes', 13.2, 3.0, '', 11.222472, 49.533840, '0000-00-00'),
('FUW1_11', 'Über den Steinberg zur "Bitterbachschlucht"', 13.2, 3.0, '', 11.258472, 49.514015, '0000-00-00'),
('FUW1_12', 'Durch stille Waldungen im Neunhofer Land', 15.4, 3.5, '', 11.231303, 49.554291, '0000-00-00'),
('FUW1_13', 'Zu den Steinbrüchen am Kornberg', 11.0, 2.5, '', 11.099428, 49.374722, '0000-00-00'),
('FUW1_14', 'Übers Hintere Wernloch zum Glasersberg', 8.8, 2.0, '', 11.145067, 49.353420, '0000-00-00'),
('FUW1_15', 'Über den Hutberg nach Birnthon', 15.4, 3.5, '', 11.196014, 49.418816, '0000-00-00'),
('FUW1_16', 'Zum Hubertusbrünnlein und nach Moosbach', 13.2, 3.0, '', 11.214278, 49.375057, '0000-00-00'),
('FUW1_17', 'Rund um den zweiten Schwarzachdurchbruch', 8.8, 2.0, '', 11.245383, 49.357964, '2009-05-22'),
('FUW1_18', 'Über Haimendorf zum Moritzberg', 11.0, 2.5, '', 11.268475, 49.471554, '0000-00-00'),
('FUW1_19', 'Zu den Rhätschluchten um Altdorf', 13.2, 3.0, '', 11.356403, 49.385777, '0000-00-00'),
('FUW1_20', 'Quer durch den Lorenzer Wald', 15.4, 3.5, '', 11.195428, 49.419353, '0000-00-00'),
('FUW1_21', 'Auf den Dillenberg bei Cadolzburg', 15.4, 3.5, '', 10.851978, 49.458084, '0000-00-00'),
('FUW1_22', 'Durch den "Klingengraben" nach Weihersbuch', 11.0, 2.5, '', 11.015539, 49.414665, '0000-00-00'),
('FUW1_23', 'Vom Zwieselgrund zum Locher Grund', 11.0, 2.5, '', 10.969706, 49.391693, '0000-00-00'),
('FUW1_24', 'Vom Fembachtal zum vorgeschichtlichen Grabhügel im Hardtwald', 8.8, 2.0, '', 10.855361, 49.509254, '0000-00-00'),
('FUW1_25', 'Zum Zimmermannsweiher im Kammerholz', 11.0, 2.5, '', 10.801106, 49.496147, '0000-00-00'),
('FUW1_26', 'Zwischen Aurach und Fembach', 15.4, 3.5, '', 10.769106, 49.535423, '0000-00-00'),
('FUW1_27', 'Rund um den Glatzenstein', 6.6, 1.5, '', 11.070814, 49.681808, '0000-00-00'),
('FUW1_28', 'Um den Siegersdorfer Talkessel', 8.8, 2.0, '', 11.375339, 49.553738, '0000-00-00'),
('FUW1_29', 'Zum "Hansgörgl" und "Glatzenstein"', 11.0, 2.5, '', 11.375339, 49.553738, '0000-00-00'),
('FUW1_30', 'Zur Festungsruine am Rothenberg', 4.4, 1.0, '', 11.374633, 49.558662, '0000-00-00'),
('FUW1_31', 'Rund um den Osternoher Talkessel', 13.2, 3.0, '', 11.377383, 49.587139, '0000-00-00'),
('FUW1_32', 'Auf den Hienberg und durchs Osternoher Tal', 8.8, 2.0, '', 11.369833, 49.586449, '0000-00-00'),
('FUW1_33', 'Von Velden ins Ankatal', 13.2, 3.0, '', 11.510744, 49.613075, '0000-00-00'),
('FUW1_34', 'Vom Hohenstein auf Höhenwegen über dem Pegnitztal', 11.0, 2.5, '', 11.424200, 49.586803, '0000-00-00'),
('FUW1_35', 'Vom Hohenstein zum Schloßberg', 19.8, 4.5, '', 11.424200, 49.586803, '0000-00-00'),
('FUW1_36', 'Über Sigrizberg durchs "Enge Tal" und über den "Vogelherd"', 11.0, 2.5, '', 11.474764, 49.585899, '0000-00-00'),
('FUW1_37', 'Nach Kreppling und zur alten Griesmühle', 8.8, 2.0, '', 11.480597, 49.600693, '0000-00-00'),
('FUW1_38', 'Rund um den Pleßel- und Leitenberg', 13.2, 3.0, '', 11.512603, 49.502949, '0000-00-00'),
('FUW1_39', 'Von Bürtel aus in die Felsenwelt des "Schwarzen Brandes"', 8.8, 2.0, '', 11.540364, 49.529949, '0000-00-00'),
('FUW1_40', 'Vom Hirschbachtal nach Hubmersberg', 11.0, 2.5, '', 11.488747, 49.532223, '0000-00-00'),
('FUW1_41', 'Rund um die "Eschenbacher Koppe"', 11.0, 2.5, '', 11.488747, 49.532223, '0000-00-00'),
('FUW1_42', 'Rings um den Atzelstein', 13.2, 3.0, '', 11.508183, 49.542770, '0000-00-00'),
('FUW1_43', 'Zum "Prellstein" und zum "Mittagsfelsen" im "Schwarzen Brand"', 8.8, 2.0, '', 11.540842, 49.556328, '0000-00-00');
