Installation
------------

-	Sämtliche Dateien in ein beliebiges StudIP-Unterverzeichnis kopieren.

-	Tabelle für die WAP-Sessionverwaltung in die Datenbank einfügen:
	create table wap_sessions (user_id CHAR(32) NOT NULL, session_id CHAR(32) NOT NULL, creation_time DATETIME);
	

Benutzung
---------

-	Mit einem WAP-Browser (z.B. von www.yourwap.com) die index.php aufrufen.



Bemerkungen
-----------

-	Bemerkung zum WAP-Emulator von yourwap.com:
	Falls man eine lokale StudIP-Installation mit eigenem Apache-Server nutzt,
	muss die (leider implementierte) WebServer-Funktionalität des
	yourwap.com-Browsers z.B. mittels Firewall unterbunden werden.
	Es gibt natürlich auch noch viele weitere on- und off-line WAP-Emulatoren
	im Netz, jedoch unterstützt der yourwap.com-Browser gleich mehrere
	Handy-Typen.
