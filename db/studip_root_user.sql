# Anlegen des Benutzers root 

# Benutzer: root@studip ; Password: testing
INSERT INTO auth_user_md5 VALUES( '76ed43ef286fb55cf9e41beadb484a9f', 'root@studip', 'ae2b1fca515949e5d54fb22b8ed95575', 'root', 'Root', 'Studip', 'root@localhost');
INSERT INTO user_info SET user_id ='76ed43ef286fb55cf9e41beadb484a9f';