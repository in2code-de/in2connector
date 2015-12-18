CREATE TABLE tx_in2connector_domain_model_soapconnection (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT 0 NOT NULL,
	package varchar(255) DEFAULT '' NOT NULL,
	identity_key varchar(255) DEFAULT '' NOT NULL,
	PRIMARY KEY (uid)
);
CREATE TABLE tx_in2connector_domain_model_ldapconnection (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT 0 NOT NULL,
	package varchar(255) DEFAULT '' NOT NULL,
	identity_key varchar(255) DEFAULT '' NOT NULL,
	PRIMARY KEY (uid)
);
