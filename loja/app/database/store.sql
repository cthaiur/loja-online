BEGIN TRANSACTION;
CREATE TABLE eco_action (
    id integer primary key NOT NULL,
    name varchar(250),
    remote_method varchar(250)
);


CREATE TABLE eco_country (
    id integer primary key NOT NULL,
    name varchar(100),
    iso varchar(100),
    iso3 varchar(100)
);


CREATE TABLE eco_customer (
    id integer primary key NOT NULL,
    name varchar(100),
    email varchar(100),
    password varchar(100),
    document varchar(100),
    phone varchar(100),
    address varchar(100),
    number varchar(250),
    complement varchar(100),
    neighborhood varchar(100),
    postal varchar(100),
    city varchar(100),
    state varchar(100),
    country_id integer REFERENCES eco_country(id),
    active char(1) DEFAULT 'N',
    created_at timestamp,
    obs varchar(250)
);

CREATE TABLE eco_payment_status (
    id integer primary key NOT NULL,
    description varchar(100),
    isfinal character(1),
    color varchar(250)
);

CREATE TABLE eco_payment_type (
    id integer primary key NOT NULL,
    description varchar(100),
    languages varchar(250),
    icon varchar(250),
    url varchar(250),
    information varchar(250)
);

CREATE TABLE eco_product (
    id integer primary key NOT NULL,
    description varchar(100),
    url varchar(100),
    amount integer,
    price float,
    currency varchar(40),
    image varchar(250),
    languages varchar(250),
    paymenttypes varchar(250),
    has_shipping character(1),
    width float,
    height float,
    length float,
    weight float,
    shipping_cost float,
    details varchar(4000),
    active character(1),
    opinions varchar(4000),
    confirmation_mail varchar(4000),
    tag varchar(250)
);

CREATE TABLE eco_coupon (
    id integer primary key NOT NULL,
    email varchar(250),
    product_id integer REFERENCES eco_product(id),
    expiration date,
    used character(1),
    discount float
);


CREATE TABLE eco_product_action (
    id integer primary key NOT NULL,
    product_id integer REFERENCES eco_product(id),
    action_id integer REFERENCES eco_action(id)
);

CREATE TABLE eco_product_requirement (
    id integer primary key NOT NULL,
    product_id integer NOT NULL REFERENCES eco_product(id),
    requirement_id integer NOT NULL REFERENCES eco_product(id)
);


CREATE TABLE eco_transaction (
    id integer primary key NOT NULL,
    operation_date date,
    external_id varchar(100),
    quantity integer,
    value float,
    customer_id integer REFERENCES eco_customer(id),
    product_id integer REFERENCES eco_product(id),
    paymentstatus_id integer REFERENCES eco_payment_status(id),
    paymenttype_id integer REFERENCES eco_payment_type(id),
    token varchar(250),
    shipping_cost float,
    total float,
    shipping_code varchar(250),
    operation_time timestamp,
    obs varchar(250)
);


CREATE TABLE eco_transaction_action (
    id integer primary key NOT NULL,
    transaction_id integer REFERENCES eco_transaction(id),
    action_id integer REFERENCES eco_action(id),
    process_time timestamp
);

CREATE TABLE eco_user (
    id integer primary key NOT NULL,
    name varchar(100),
    email varchar(100),
    password varchar(100),
    role varchar(100)
);

CREATE VIEW view_pending_actions AS
 SELECT eco_transaction.id AS transaction_id,
    eco_product.description AS product_description,
    eco_transaction.shipping_code,
    eco_transaction.external_id,
    eco_transaction.operation_date,
    eco_action.id AS action_id,
    eco_action.remote_method,
    eco_customer.name AS customer_name,
    eco_customer.email AS customer_email,
    eco_product.id AS product_id,
    eco_product.confirmation_mail
   FROM eco_transaction,
    eco_customer,
    eco_product,
    eco_product_action,
    eco_action
  WHERE ((eco_transaction.product_id = eco_product.id) AND (eco_transaction.customer_id = eco_customer.id) AND (eco_product_action.product_id = eco_product.id) AND (eco_product_action.action_id = eco_action.id) AND (eco_transaction.paymentstatus_id IN (3,4)) AND (NOT (EXISTS ( SELECT 1
           FROM eco_transaction sub_tr,
            eco_transaction_action sub_tr_act,
            eco_action sub_act
          WHERE ((sub_tr_act.transaction_id = sub_tr.id) AND (sub_tr_act.action_id = sub_act.id) AND (sub_tr.id = eco_transaction.id) AND (sub_act.id = eco_action.id))))))
  ORDER BY eco_transaction.id, eco_action.id;


CREATE VIEW view_processed_actions AS
 SELECT eco_transaction.id,
    eco_transaction.external_id,
    eco_customer.name AS customer_name,
    eco_product.description AS product_description,
    eco_action.name AS action,
    eco_transaction_action.process_time
   FROM eco_transaction,
    eco_transaction_action,
    eco_customer,
    eco_product,
    eco_action
  WHERE ((eco_transaction.id = eco_transaction_action.transaction_id) AND (eco_transaction.customer_id = eco_customer.id) AND (eco_transaction.product_id = eco_product.id) AND (eco_transaction_action.action_id = eco_action.id))
  ORDER BY eco_transaction_action.process_time DESC;


CREATE VIEW view_transaction AS
 SELECT et.id,
    et.operation_date,
    et.external_id,
    et.quantity,
    et.value,
    et.customer_id,
    et.product_id,
    et.paymentstatus_id,
    et.paymenttype_id,
    et.token,
    et.shipping_cost,
    et.total,
    et.shipping_code,
    et.operation_time,
    ec.name AS customer_name,
    ec.email AS customer_email,
    ec.document AS customer_document,
    ep.description AS product_description
   FROM eco_transaction et,
    eco_customer ec,
    eco_product ep
  WHERE ((et.customer_id = ec.id) AND (et.product_id = ep.id));

INSERT INTO eco_action VALUES(1,'Envia e-mail padrão após compra confirmada','sendConfirmationMail');
INSERT INTO eco_action VALUES(2,'Envia outra coisa','sendAnotherThing');

INSERT INTO eco_country VALUES(56,'Afghanistan','AF','AFG');
INSERT INTO eco_country VALUES(57,'Albania','AL','ALB');
INSERT INTO eco_country VALUES(58,'Algeria','DZ','DZA');
INSERT INTO eco_country VALUES(59,'American Samoa','AS','ASM');
INSERT INTO eco_country VALUES(60,'Andorra','AD','AND');
INSERT INTO eco_country VALUES(61,'Angola','AO','AGO');
INSERT INTO eco_country VALUES(62,'Anguilla','AI','AIA');
INSERT INTO eco_country VALUES(63,'Antarctica','AQ',NULL);
INSERT INTO eco_country VALUES(64,'Antigua and Barbuda','AG','ATG');
INSERT INTO eco_country VALUES(65,'Argentina','AR','ARG');
INSERT INTO eco_country VALUES(66,'Armenia','AM','ARM');
INSERT INTO eco_country VALUES(67,'Aruba','AW','ABW');
INSERT INTO eco_country VALUES(68,'Australia','AU','AUS');
INSERT INTO eco_country VALUES(69,'Austria','AT','AUT');
INSERT INTO eco_country VALUES(70,'Azerbaijan','AZ','AZE');
INSERT INTO eco_country VALUES(71,'Bahamas','BS','BHS');
INSERT INTO eco_country VALUES(72,'Bahrain','BH','BHR');
INSERT INTO eco_country VALUES(73,'Bangladesh','BD','BGD');
INSERT INTO eco_country VALUES(74,'Barbados','BB','BRB');
INSERT INTO eco_country VALUES(75,'Belarus','BY','BLR');
INSERT INTO eco_country VALUES(76,'Belgium','BE','BEL');
INSERT INTO eco_country VALUES(77,'Belize','BZ','BLZ');
INSERT INTO eco_country VALUES(78,'Benin','BJ','BEN');
INSERT INTO eco_country VALUES(79,'Bermuda','BM','BMU');
INSERT INTO eco_country VALUES(80,'Bhutan','BT','BTN');
INSERT INTO eco_country VALUES(81,'Bolivia','BO','BOL');
INSERT INTO eco_country VALUES(82,'Bosnia and Herzegovina','BA','BIH');
INSERT INTO eco_country VALUES(83,'Botswana','BW','BWA');
INSERT INTO eco_country VALUES(84,'Bouvet Island','BV',NULL);
INSERT INTO eco_country VALUES(85,'Brazil','BR','BRA');
INSERT INTO eco_country VALUES(86,'British Indian Ocean Territory','IO',NULL);
INSERT INTO eco_country VALUES(87,'Brunei Darussalam','BN','BRN');
INSERT INTO eco_country VALUES(88,'Bulgaria','BG','BGR');
INSERT INTO eco_country VALUES(89,'Burkina Faso','BF','BFA');
INSERT INTO eco_country VALUES(90,'Burundi','BI','BDI');
INSERT INTO eco_country VALUES(91,'Cambodia','KH','KHM');
INSERT INTO eco_country VALUES(92,'Cameroon','CM','CMR');
INSERT INTO eco_country VALUES(93,'Canada','CA','CAN');
INSERT INTO eco_country VALUES(94,'Cape Verde','CV','CPV');
INSERT INTO eco_country VALUES(95,'Cayman Islands','KY','CYM');
INSERT INTO eco_country VALUES(96,'Central African Republic','CF','CAF');
INSERT INTO eco_country VALUES(97,'Chad','TD','TCD');
INSERT INTO eco_country VALUES(98,'Chile','CL','CHL');
INSERT INTO eco_country VALUES(99,'China','CN','CHN');
INSERT INTO eco_country VALUES(100,'Christmas Island','CX',NULL);
INSERT INTO eco_country VALUES(101,'Cocos (Keeling) Islands','CC',NULL);
INSERT INTO eco_country VALUES(102,'Colombia','CO','COL');
INSERT INTO eco_country VALUES(103,'Comoros','KM','COM');
INSERT INTO eco_country VALUES(104,'Congo','CG','COG');
INSERT INTO eco_country VALUES(105,'Congo, the Democratic Republic of the','CD','COD');
INSERT INTO eco_country VALUES(106,'Cook Islands','CK','COK');
INSERT INTO eco_country VALUES(107,'Costa Rica','CR','CRI');
INSERT INTO eco_country VALUES(108,'Cote D''Ivoire','CI','CIV');
INSERT INTO eco_country VALUES(109,'Croatia','HR','HRV');
INSERT INTO eco_country VALUES(110,'Cuba','CU','CUB');
INSERT INTO eco_country VALUES(111,'Cyprus','CY','CYP');
INSERT INTO eco_country VALUES(112,'Czech Republic','CZ','CZE');
INSERT INTO eco_country VALUES(113,'Denmark','DK','DNK');
INSERT INTO eco_country VALUES(114,'Djibouti','DJ','DJI');
INSERT INTO eco_country VALUES(115,'Dominica','DM','DMA');
INSERT INTO eco_country VALUES(116,'Dominican Republic','DO','DOM');
INSERT INTO eco_country VALUES(117,'Ecuador','EC','ECU');
INSERT INTO eco_country VALUES(118,'Egypt','EG','EGY');
INSERT INTO eco_country VALUES(119,'El Salvador','SV','SLV');
INSERT INTO eco_country VALUES(120,'Equatorial Guinea','GQ','GNQ');
INSERT INTO eco_country VALUES(121,'Eritrea','ER','ERI');
INSERT INTO eco_country VALUES(122,'Estonia','EE','EST');
INSERT INTO eco_country VALUES(123,'Ethiopia','ET','ETH');
INSERT INTO eco_country VALUES(124,'Falkland Islands (Malvinas)','FK','FLK');
INSERT INTO eco_country VALUES(125,'Faroe Islands','FO','FRO');
INSERT INTO eco_country VALUES(126,'Fiji','FJ','FJI');
INSERT INTO eco_country VALUES(127,'Finland','FI','FIN');
INSERT INTO eco_country VALUES(128,'France','FR','FRA');
INSERT INTO eco_country VALUES(129,'French Guiana','GF','GUF');
INSERT INTO eco_country VALUES(130,'French Polynesia','PF','PYF');
INSERT INTO eco_country VALUES(131,'French Southern Territories','TF',NULL);
INSERT INTO eco_country VALUES(132,'Gabon','GA','GAB');
INSERT INTO eco_country VALUES(133,'Gambia','GM','GMB');
INSERT INTO eco_country VALUES(134,'Georgia','GE','GEO');
INSERT INTO eco_country VALUES(135,'Germany','DE','DEU');
INSERT INTO eco_country VALUES(136,'Ghana','GH','GHA');
INSERT INTO eco_country VALUES(137,'Gibraltar','GI','GIB');
INSERT INTO eco_country VALUES(138,'Greece','GR','GRC');
INSERT INTO eco_country VALUES(139,'Greenland','GL','GRL');
INSERT INTO eco_country VALUES(140,'Grenada','GD','GRD');
INSERT INTO eco_country VALUES(141,'Guadeloupe','GP','GLP');
INSERT INTO eco_country VALUES(142,'Guam','GU','GUM');
INSERT INTO eco_country VALUES(143,'Guatemala','GT','GTM');
INSERT INTO eco_country VALUES(144,'Guinea','GN','GIN');
INSERT INTO eco_country VALUES(145,'Guinea-Bissau','GW','GNB');
INSERT INTO eco_country VALUES(146,'Guyana','GY','GUY');
INSERT INTO eco_country VALUES(147,'Haiti','HT','HTI');
INSERT INTO eco_country VALUES(148,'Heard Island and Mcdonald Islands','HM',NULL);
INSERT INTO eco_country VALUES(149,'Holy See (Vatican City State)','VA','VAT');
INSERT INTO eco_country VALUES(150,'Honduras','HN','HND');
INSERT INTO eco_country VALUES(151,'Hong Kong','HK','HKG');
INSERT INTO eco_country VALUES(152,'Hungary','HU','HUN');
INSERT INTO eco_country VALUES(153,'Iceland','IS','ISL');
INSERT INTO eco_country VALUES(154,'India','IN','IND');
INSERT INTO eco_country VALUES(155,'Indonesia','ID','IDN');
INSERT INTO eco_country VALUES(156,'Iran, Islamic Republic of','IR','IRN');
INSERT INTO eco_country VALUES(157,'Iraq','IQ','IRQ');
INSERT INTO eco_country VALUES(158,'Ireland','IE','IRL');
INSERT INTO eco_country VALUES(159,'Israel','IL','ISR');
INSERT INTO eco_country VALUES(160,'Italy','IT','ITA');
INSERT INTO eco_country VALUES(161,'Jamaica','JM','JAM');
INSERT INTO eco_country VALUES(162,'Japan','JP','JPN');
INSERT INTO eco_country VALUES(163,'Jordan','JO','JOR');
INSERT INTO eco_country VALUES(164,'Kazakhstan','KZ','KAZ');
INSERT INTO eco_country VALUES(165,'Kenya','KE','KEN');
INSERT INTO eco_country VALUES(166,'Kiribati','KI','KIR');
INSERT INTO eco_country VALUES(167,'Korea, Democratic People''s Republic of','KP','PRK');
INSERT INTO eco_country VALUES(168,'Korea, Republic of','KR','KOR');
INSERT INTO eco_country VALUES(169,'Kuwait','KW','KWT');
INSERT INTO eco_country VALUES(170,'Kyrgyzstan','KG','KGZ');
INSERT INTO eco_country VALUES(171,'Lao People''s Democratic Republic','LA','LAO');
INSERT INTO eco_country VALUES(172,'Latvia','LV','LVA');
INSERT INTO eco_country VALUES(173,'Lebanon','LB','LBN');
INSERT INTO eco_country VALUES(174,'Lesotho','LS','LSO');
INSERT INTO eco_country VALUES(175,'Liberia','LR','LBR');
INSERT INTO eco_country VALUES(176,'Libyan Arab Jamahiriya','LY','LBY');
INSERT INTO eco_country VALUES(177,'Liechtenstein','LI','LIE');
INSERT INTO eco_country VALUES(178,'Lithuania','LT','LTU');
INSERT INTO eco_country VALUES(179,'Luxembourg','LU','LUX');
INSERT INTO eco_country VALUES(180,'Macao','MO','MAC');
INSERT INTO eco_country VALUES(181,'Macedonia, the Former Yugoslav Republic of','MK','MKD');
INSERT INTO eco_country VALUES(182,'Madagascar','MG','MDG');
INSERT INTO eco_country VALUES(183,'Malawi','MW','MWI');
INSERT INTO eco_country VALUES(184,'Malaysia','MY','MYS');
INSERT INTO eco_country VALUES(185,'Maldives','MV','MDV');
INSERT INTO eco_country VALUES(186,'Mali','ML','MLI');
INSERT INTO eco_country VALUES(187,'Malta','MT','MLT');
INSERT INTO eco_country VALUES(188,'Marshall Islands','MH','MHL');
INSERT INTO eco_country VALUES(189,'Martinique','MQ','MTQ');
INSERT INTO eco_country VALUES(190,'Mauritania','MR','MRT');
INSERT INTO eco_country VALUES(191,'Mauritius','MU','MUS');
INSERT INTO eco_country VALUES(192,'Mayotte','YT',NULL);
INSERT INTO eco_country VALUES(193,'Mexico','MX','MEX');
INSERT INTO eco_country VALUES(194,'Micronesia, Federated States of','FM','FSM');
INSERT INTO eco_country VALUES(195,'Moldova, Republic of','MD','MDA');
INSERT INTO eco_country VALUES(196,'Monaco','MC','MCO');
INSERT INTO eco_country VALUES(197,'Mongolia','MN','MNG');
INSERT INTO eco_country VALUES(198,'Montserrat','MS','MSR');
INSERT INTO eco_country VALUES(199,'Morocco','MA','MAR');
INSERT INTO eco_country VALUES(200,'Mozambique','MZ','MOZ');
INSERT INTO eco_country VALUES(201,'Myanmar','MM','MMR');
INSERT INTO eco_country VALUES(202,'Namibia','NA','NAM');
INSERT INTO eco_country VALUES(203,'Nauru','NR','NRU');
INSERT INTO eco_country VALUES(204,'Nepal','NP','NPL');
INSERT INTO eco_country VALUES(205,'Netherlands','NL','NLD');
INSERT INTO eco_country VALUES(206,'Netherlands Antilles','AN','ANT');
INSERT INTO eco_country VALUES(207,'New Caledonia','NC','NCL');
INSERT INTO eco_country VALUES(208,'New Zealand','NZ','NZL');
INSERT INTO eco_country VALUES(209,'Nicaragua','NI','NIC');
INSERT INTO eco_country VALUES(210,'Niger','NE','NER');
INSERT INTO eco_country VALUES(211,'Nigeria','NG','NGA');
INSERT INTO eco_country VALUES(212,'Niue','NU','NIU');
INSERT INTO eco_country VALUES(213,'Norfolk Island','NF','NFK');
INSERT INTO eco_country VALUES(214,'Northern Mariana Islands','MP','MNP');
INSERT INTO eco_country VALUES(215,'Norway','NO','NOR');
INSERT INTO eco_country VALUES(216,'Oman','OM','OMN');
INSERT INTO eco_country VALUES(217,'Pakistan','PK','PAK');
INSERT INTO eco_country VALUES(218,'Palau','PW','PLW');
INSERT INTO eco_country VALUES(219,'Palestinian Territory, Occupied','PS',NULL);
INSERT INTO eco_country VALUES(220,'Panama','PA','PAN');
INSERT INTO eco_country VALUES(221,'Papua New Guinea','PG','PNG');
INSERT INTO eco_country VALUES(222,'Paraguay','PY','PRY');
INSERT INTO eco_country VALUES(223,'Peru','PE','PER');
INSERT INTO eco_country VALUES(224,'Philippines','PH','PHL');
INSERT INTO eco_country VALUES(225,'Pitcairn','PN','PCN');
INSERT INTO eco_country VALUES(226,'Poland','PL','POL');
INSERT INTO eco_country VALUES(227,'Portugal','PT','PRT');
INSERT INTO eco_country VALUES(228,'Puerto Rico','PR','PRI');
INSERT INTO eco_country VALUES(229,'Qatar','QA','QAT');
INSERT INTO eco_country VALUES(230,'Reunion','RE','REU');
INSERT INTO eco_country VALUES(231,'Romania','RO','ROM');
INSERT INTO eco_country VALUES(232,'Russian Federation','RU','RUS');
INSERT INTO eco_country VALUES(233,'Rwanda','RW','RWA');
INSERT INTO eco_country VALUES(234,'Saint Helena','SH','SHN');
INSERT INTO eco_country VALUES(235,'Saint Kitts and Nevis','KN','KNA');
INSERT INTO eco_country VALUES(236,'Saint Lucia','LC','LCA');
INSERT INTO eco_country VALUES(237,'Saint Pierre and Miquelon','PM','SPM');
INSERT INTO eco_country VALUES(238,'Saint Vincent and the Grenadines','VC','VCT');
INSERT INTO eco_country VALUES(239,'Samoa','WS','WSM');
INSERT INTO eco_country VALUES(240,'San Marino','SM','SMR');
INSERT INTO eco_country VALUES(241,'Sao Tome and Principe','ST','STP');
INSERT INTO eco_country VALUES(242,'Saudi Arabia','SA','SAU');
INSERT INTO eco_country VALUES(243,'Senegal','SN','SEN');
INSERT INTO eco_country VALUES(244,'Serbia and Montenegro','CS',NULL);
INSERT INTO eco_country VALUES(245,'Seychelles','SC','SYC');
INSERT INTO eco_country VALUES(246,'Sierra Leone','SL','SLE');
INSERT INTO eco_country VALUES(247,'Singapore','SG','SGP');
INSERT INTO eco_country VALUES(248,'Slovakia','SK','SVK');
INSERT INTO eco_country VALUES(249,'Slovenia','SI','SVN');
INSERT INTO eco_country VALUES(250,'Solomon Islands','SB','SLB');
INSERT INTO eco_country VALUES(251,'Somalia','SO','SOM');
INSERT INTO eco_country VALUES(252,'South Africa','ZA','ZAF');
INSERT INTO eco_country VALUES(253,'South Georgia and the South Sandwich Islands','GS',NULL);
INSERT INTO eco_country VALUES(254,'Spain','ES','ESP');
INSERT INTO eco_country VALUES(255,'Sri Lanka','LK','LKA');
INSERT INTO eco_country VALUES(256,'Sudan','SD','SDN');
INSERT INTO eco_country VALUES(257,'Suriname','SR','SUR');
INSERT INTO eco_country VALUES(258,'Svalbard and Jan Mayen','SJ','SJM');
INSERT INTO eco_country VALUES(259,'Swaziland','SZ','SWZ');
INSERT INTO eco_country VALUES(260,'Sweden','SE','SWE');
INSERT INTO eco_country VALUES(261,'Switzerland','CH','CHE');
INSERT INTO eco_country VALUES(262,'Syrian Arab Republic','SY','SYR');
INSERT INTO eco_country VALUES(263,'Taiwan, Province of China','TW','TWN');
INSERT INTO eco_country VALUES(264,'Tajikistan','TJ','TJK');
INSERT INTO eco_country VALUES(265,'Tanzania, United Republic of','TZ','TZA');
INSERT INTO eco_country VALUES(266,'Thailand','TH','THA');
INSERT INTO eco_country VALUES(267,'Timor-Leste','TL',NULL);
INSERT INTO eco_country VALUES(268,'Togo','TG','TGO');
INSERT INTO eco_country VALUES(269,'Tokelau','TK','TKL');
INSERT INTO eco_country VALUES(270,'Tonga','TO','TON');
INSERT INTO eco_country VALUES(271,'Trinidad and Tobago','TT','TTO');
INSERT INTO eco_country VALUES(272,'Tunisia','TN','TUN');
INSERT INTO eco_country VALUES(273,'Turkey','TR','TUR');
INSERT INTO eco_country VALUES(274,'Turkmenistan','TM','TKM');
INSERT INTO eco_country VALUES(275,'Turks and Caicos Islands','TC','TCA');
INSERT INTO eco_country VALUES(276,'Tuvalu','TV','TUV');
INSERT INTO eco_country VALUES(277,'Uganda','UG','UGA');
INSERT INTO eco_country VALUES(278,'Ukraine','UA','UKR');
INSERT INTO eco_country VALUES(279,'United Arab Emirates','AE','ARE');
INSERT INTO eco_country VALUES(280,'United Kingdom','GB','GBR');
INSERT INTO eco_country VALUES(281,'United States','US','USA');
INSERT INTO eco_country VALUES(282,'United States Minor Outlying Islands','UM',NULL);
INSERT INTO eco_country VALUES(283,'Uruguay','UY','URY');
INSERT INTO eco_country VALUES(284,'Uzbekistan','UZ','UZB');
INSERT INTO eco_country VALUES(285,'Vanuatu','VU','VUT');
INSERT INTO eco_country VALUES(286,'Venezuela','VE','VEN');
INSERT INTO eco_country VALUES(287,'Viet Nam','VN','VNM');
INSERT INTO eco_country VALUES(288,'Virgin Islands, British','VG','VGB');
INSERT INTO eco_country VALUES(289,'Virgin Islands, U.s.','VI','VIR');
INSERT INTO eco_country VALUES(290,'Wallis and Futuna','WF','WLF');
INSERT INTO eco_country VALUES(291,'Western Sahara','EH','ESH');
INSERT INTO eco_country VALUES(292,'Yemen','YE','YEM');
INSERT INTO eco_country VALUES(293,'Zambia','ZM','ZMB');
INSERT INTO eco_country VALUES(294,'Zimbabwe','ZW','ZWE');

INSERT INTO eco_customer VALUES(1,'SUA EMPRESA LTDA','empresa@empresa.com','$2y$10$1FzclVrqgj8y7XVaPnptn.Ey49Mi4xwdvBGFFxicBhMaiLEV/qSM6','11111111111','(51)1234-5678','Rua Júlio de Castilhos','123','Casa B','Centro','95185-000','Carlos Barbosa','RS',85,'Y','2021-09-04 16:04:37',NULL);
INSERT INTO eco_customer VALUES(2,'Maria Rita','maria@teste.com','$2y$10$clK0s8L0KtThECvlqWJW8uUoh6zWVU1l/1/n7ph17OU2WOyHMtE0i','22222222222','(22)1234-5678','Rua Três de Maio','123','Casa E','Centro','24110-176','Niterói','RJ',85,'Y','2021-09-04 16:05:35',NULL);
INSERT INTO eco_customer VALUES(3,'Pedro Barbosa','pedro@teste.com','$2y$10$/.lQYiJCaMtD/kZdHpO4R.cPu7XSKfgmsdeq9FFVuOkkjwCcSlYZK','33333333333','(33)1234-5678','Rua Espírito Santo','123','Casa W','Centro','30160-030','Belo Horizonte','MG',85,'Y','2021-09-04 16:06:32',NULL);
INSERT INTO eco_customer VALUES(4,'Miguel Santos','miguel@teste.com','$2y$10$VThzElu07WksDd9k2cCt.uIdoizy7c5w1UNtK4rGnWryZNV.2iyQm','44444444444','(44)1234-5678','Rua Capitão Gouveia','123','Casa F','Praia do Meio','59010-010','Natal','RN',85,'Y','2021-09-04 16:08:41',NULL);
INSERT INTO eco_customer VALUES(5,'Laura Fonseca','laura@teste.com','$2y$10$FDlguehOBlWv4Rgg6oPbRu.vlXaalsdFVZ01sRIEEJ1sq3wRo/O1e','55555555555','(55)1234-5678','Avenida Calógeras','123','Casa C','Centro','79002-002','Campo Grande','MS',85,'Y','2021-09-04 16:09:50',NULL);

INSERT INTO eco_payment_status VALUES(1,'Waiting payment','N','#ffe380');
INSERT INTO eco_payment_status VALUES(2,'In analysis','N',NULL);
INSERT INTO eco_payment_status VALUES(3,'Paid','N','#d1e1ff');
INSERT INTO eco_payment_status VALUES(4,'Available','Y',NULL);
INSERT INTO eco_payment_status VALUES(5,'In dispute','N',NULL);
INSERT INTO eco_payment_status VALUES(6,'Returned','Y',NULL);
INSERT INTO eco_payment_status VALUES(7,'Canceled','Y','#eb9191');
INSERT INTO eco_payment_status VALUES(8,'Delivered','Y','#c3ffb3');
INSERT INTO eco_payment_status VALUES(99,'Started','N',NULL);
INSERT INTO eco_payment_status VALUES(100,'Gift','Y','#f200f5');

INSERT INTO eco_payment_type VALUES(1,'Cartão ou Boleto','pt','fa fa-globe','https://pagseguro.uol.com.br','PagSeguro: Pague com cartão de crédito, boleto, ou depósito online pelo melhor meio de pagamentos brasileiro.');
INSERT INTO eco_payment_type VALUES(2,'PayPal','pt,en','fab fa-paypal','https://www.paypal.com','Pague com cartão de crédito por meio do serviço mais conhecido do mundo.');
INSERT INTO eco_payment_type VALUES(3,'TED','pt','fa fa-money-bill',NULL,'Deposite diretamente em nossa conta corrente (Banco do Brasil) sem complicações.');

INSERT INTO eco_product VALUES(1,'Curso como ser uma pessoa melhor','/1',NULL,100.0,'R$','images/person.png','pt','1,2,3',NULL,NULL,NULL,NULL,NULL,NULL,'<div>Produto<b> {name} </b>é mt bom</div><div><br></div><ul><li>Mais isso</li><li>Mais isso</li><li>Mais isso</li><li>Mais isso</li><li>Mais isso</li></ul><p><br></p><table class="table table-bordered"><tbody><tr><td><b>a</b></td><td><b>b</b></td><td><b>c</b></td></tr><tr><td>isso</td><td>é um</td><td>teste</td></tr></tbody></table><p><br></p>','Y','<p>Produto bom</p><p>Produto legal!</p>','<p>Olá {customer_name},</p><p><br></p><p>É um prazer ter você no&nbsp;<span style="font-weight: 700;">{product_description}</span>.</p><p>Seu pagamento já foi processado, e você já pode acessar todo o conteúdo exclusivo do curso.</p><p><span style="font-size: 10pt;">Para acessar o ambiente do curso, você usará o mesmo usuário e senha da nossa loja virtual:</span><br></p><p><a href="https://ead.loja.com.br" target="_blank">https://ead.loja.com.br</a></p><p>Obrigado</p>','curso-phpoo');
INSERT INTO eco_product VALUES(2,'Como ser um gestor feliz','/2',NULL,120.0,'R$','images/manager.png','pt','1,2,3',NULL,NULL,NULL,NULL,NULL,NULL,'<ol><li>Vem com isso</li><li>Mais isso</li><li>Mais isso</li><li>Mais isso</li><li>Mais isso</li></ol>','Y',NULL,NULL,NULL);

INSERT INTO eco_product_action VALUES(5,1,1);

INSERT INTO eco_transaction VALUES(2,'2021-09-04',NULL,1,120.0,2,2,8,3,NULL,NULL,120.0,NULL,'2021-09-04 16:11:54',NULL);
INSERT INTO eco_transaction VALUES(3,'2021-09-04',NULL,1,100.0,3,1,8,3,NULL,NULL,100.0,NULL,'2021-09-04 16:12:04',NULL);
INSERT INTO eco_transaction VALUES(4,'2021-09-04',NULL,1,120.0,4,2,8,3,NULL,NULL,120.0,NULL,'2021-09-04 16:12:18',NULL);
INSERT INTO eco_transaction VALUES(5,'2021-09-04',NULL,1,100.0,5,1,1,3,NULL,NULL,100.0,NULL,'2021-09-04 16:12:28',NULL);

INSERT INTO eco_user VALUES(1,'Admin','admin','$2y$10$YstsY01IHBsD2YtThFj/7.i6BuNAlmsqbc7cNXY9jJNAJrNj3F1qq','ADMINISTRATOR');

COMMIT;
