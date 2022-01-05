drop table LivesInRE;
drop table LivesInRA;
drop table WorksIn;
drop table Cleans;
drop table DiningHallStaff;
drop table CleaningStaff;
drop table AssignRE;
drop table AssignRA;
drop table ResidenceAdvisor;
drop table Resident;
drop table Room;
drop table Grouped;
drop table Has;
drop table Manages;
drop table Residence;
drop table FrontDesk;
drop table FrontDeskStaff;
drop table DiningHall;
drop table Building;
drop table ResidentManagementOffice;

CREATE TABLE ResidentManagementOffice(
	University		varchar(50) PRIMARY KEY,
	MailingAddress	varchar(80),
	EstablishedYear integer
);

CREATE TABLE Building(
	Building_Name		varchar(50),
	Building_Address	varchar(80),
	primary key (Building_Name, Building_Address)
);

CREATE TABLE DiningHall(
	DiningHall_Name			varchar(50)	PRIMARY KEY,
	Hours								varchar(30),
	Menu								varchar(60)
);

CREATE TABLE FrontDeskStaff(
	SINum								integer		PRIMARY KEY,
	Wages								Number(*,2),
	Name								varchar(50),
	ContactInformation	varchar(50),
	University					varchar(50),
	FOREIGN KEY (University) REFERENCES ResidentManagementOffice ON DELETE CASCADE
);

--no ON UPDATE CASCADE in oracle
CREATE TABLE FrontDesk(
	MailingAddress	varchar(80)	PRIMARY KEY,
	NumOfEmployees	integer,
	SINum						integer		NOT NULL,
	FOREIGN KEY (SINum) REFERENCES FrontDeskStaff ON DELETE CASCADE
);

CREATE TABLE Residence(
	Residence_Name						varchar(50)	PRIMARY KEY,
	EstablishedYear	integer		DEFAULT 1812
);

--no ON UPDATE CASCADE in oracle
CREATE TABLE Manages(
	Residence_Name	varchar(50)	PRIMARY KEY,
	MailingAddress	varchar(80),
	FOREIGN KEY (Residence_Name) REFERENCES Residence ON DELETE CASCADE,
	FOREIGN KEY (MailingAddress) REFERENCES FrontDesk ON DELETE CASCADE
);

--no ON UPDATE CASCADE in oracle
CREATE TABLE Has(
	DiningHall_Name		varchar(50),
	Residence_Name		varchar(50),
	PRIMARY KEY (DiningHall_Name, Residence_Name),
	FOREIGN KEY (DiningHall_Name) REFERENCES DiningHall(DiningHall_Name) ON DELETE CASCADE,
	FOREIGN KEY (Residence_Name) REFERENCES Residence(Residence_Name) ON DELETE CASCADE
);

CREATE TABLE Grouped(
	Building_Address varchar(80),
	Building_Name	varchar(50),
	Residence_Name	varchar(50),
	PRIMARY KEY (Building_Address, Building_Name, Residence_Name),
	FOREIGN KEY (Building_Name, Building_Address) REFERENCES Building(Building_Name, Building_Address) ON DELETE CASCADE,
	FOREIGN KEY (Residence_Name) REFERENCES Residence(Residence_Name) ON DELETE CASCADE
);

CREATE TABLE Room(
	Address			varchar(80),
	Name				varchar(50),
	RoomNumber	integer,
	RoomType		varchar(50),
	PRIMARY KEY (Name, Address, RoomNumber),
	FOREIGN KEY (Name, Address) REFERENCES Building ON DELETE CASCADE
);

-- Merged tables
CREATE TABLE Resident(
	StudentNumber 			integer PRIMARY KEY,
	Name								varchar(50),
	ContactInformation	varchar(50),
	PreferenceList			varchar(80),
	Age									integer,
	University					varchar(50),
	FOREIGN KEY (University) REFERENCES ResidentManagementOffice ON DELETE CASCADE
);

CREATE TABLE ResidenceAdvisor(
	StudentNumber				integer	PRIMARY KEY,
	Name								varchar(50),
	ContactInformation	varchar(50),
	SINum									integer,
	University					varchar(50),
	Age									integer,
	FOREIGN KEY (University) REFERENCES ResidentManagementOffice ON DELETE CASCADE
);

--no ON UPDATE CASCADE in oracle
CREATE TABLE AssignRE(
	Address				varchar(80),
	RoomNumber		integer,
	Name					varchar(50),
	StudentNumberRE			integer,
	PRIMARY KEY (StudentNumberRE, Address, RoomNumber, Name),
	FOREIGN KEY (Name, Address, RoomNumber) REFERENCES Room ON DELETE CASCADE,
	FOREIGN KEY (StudentNumberRE) REFERENCES Resident(StudentNumber) ON DELETE CASCADE
);

--no ON UPDATE CASCADE in oracle
CREATE TABLE AssignRA(
	Address				varchar(80),
	RoomNumber		integer,
	Name					varchar(50),
	StudentNumberRA			integer,
	PRIMARY KEY (StudentNumberRA, Address, RoomNumber, Name),
	FOREIGN KEY (Name, Address, RoomNumber) REFERENCES Room ON DELETE CASCADE,
	FOREIGN KEY (StudentNumberRA) REFERENCES ResidenceAdvisor(StudentNumber) ON DELETE CASCADE
);

--no ON UPDATE CASCADE in oracle
CREATE TABLE CleaningStaff(
	SINum								integer		PRIMARY KEY,
	Wages								Number(*,2),
	Name								varchar(50),
	ContactInformation	varchar(50),
	University					varchar(50),
	FOREIGN KEY (University) REFERENCES ResidentManagementOffice ON DELETE CASCADE
);

--no ON UPDATE CASCADE in oracle
CREATE TABLE DiningHallStaff(
	SINum								integer		PRIMARY KEY,
	Wages								Number(*,2),
	Name								varchar(50),
	ContactInformation	varchar(50),
	University					varchar(50),
	FOREIGN KEY (University) REFERENCES ResidentManagementOffice ON DELETE CASCADE
);

--no ON UPDATE CASCADE in oracle
CREATE TABLE Cleans(
	SINum					integer		NOT NULL,
	Address				varchar(80),
	Name					varchar(50)	NOT NULL,
	PRIMARY KEY (SINum, Address, Name),
	FOREIGN KEY (SINum) REFERENCES CleaningStaff ON DELETE CASCADE,
	FOREIGN KEY (Name, Address) REFERENCES Building ON DELETE CASCADE
);

--no ON UPDATE CASCADE in oracle
CREATE TABLE WorksIn(
	SINum				integer,
	Name			varchar(50),
	PRIMARY KEY		(SINum, Name),
	FOREIGN KEY		(SINum) REFERENCES DiningHallStaff ON DELETE CASCADE,
	FOREIGN KEY		(Name) REFERENCES DiningHall ON DELETE CASCADE
);

CREATE TABLE LivesInRE(
	Name								varchar(50),
	StudentNumberRE			integer,
	PRIMARY KEY (Name, StudentNumberRE),
	FOREIGN KEY (StudentNumberRE) REFERENCES Resident(StudentNumber) ON DELETE CASCADE,
	FOREIGN KEY (Name) REFERENCES Residence ON DELETE CASCADE
);

CREATE TABLE LivesInRA(
	Name								varchar(50),
	StudentNumberRA			integer,
	PRIMARY KEY (Name, StudentNumberRA),
	FOREIGN KEY (StudentNumberRA) REFERENCES ResidenceAdvisor(StudentNumber) ON DELETE CASCADE,
	FOREIGN KEY (Name) REFERENCES Residence ON DELETE CASCADE
);

insert into ResidentManagementOffice
values('UNI1', 'UNI1Address', 1857);

insert into ResidentManagementOffice
values('UNI2', 'UNI2Address', 1904);

insert into ResidentManagementOffice
values('UNI3', 'UNI3Address', 1956);

insert into ResidentManagementOffice
values('UNI4', 'UNI4Address', 1942);

insert into ResidentManagementOffice
values('UNI5', 'UNI5Address', 1985);

insert into Building
values('B1', 'B1Address');

insert into Building
values('B2', 'B2Address');

insert into Building
values('B3', 'B3Address');

insert into Building
values('B4', 'B4Address');

insert into Building
values('B5', 'B5Address');

insert into DiningHall
values('DH1', '24/7', 'N/A');

insert into DiningHall
values('DH2', '24/7', 'N/A');

insert into DiningHall
values('DH3', '24/7', 'N/A');

insert into DiningHall
values('DH4', '24/7', 'N/A');

insert into DiningHall
values('DH5', '24/7', 'N/A');

insert into FrontDeskStaff
values(900000001, 15.11, 'FDS1', '123 456 1111', 'UNI1');

insert into FrontDeskStaff
values(900000002, 15.20, 'FDS2', '123 456 1112', 'UNI1');

insert into FrontDeskStaff
values(900000003, 14.96, 'FDS3', '123 456 1113', 'UNI1');

insert into FrontDeskStaff
values(900000004, 15.25, 'FDS4', '123 456 1114', 'UNI1');

insert into FrontDeskStaff
values(900000005, 14.65, 'FDS5', '123 456 1115', 'UNI1');

insert into FrontDesk
values('FDMailing1', 1, 900000001);

insert into FrontDesk
values('FDMailing2', 1, 900000002);

insert into FrontDesk
values('FDMailing3', 1, 900000003);

insert into FrontDesk
values('FDMailing4', 1, 900000004);

insert into FrontDesk
values('FDMailing5', 1, 900000005);

insert into Residence
values('R1', 1920);

insert into Residence
values('R2', 1930);

insert into Residence
values('R3', 1940);

insert into Residence
values('R4', 1950);

insert into Residence
values('R5', 1960);

insert into Residence
values('R6', 1920);

insert into Residence
values('R7', 1930);

insert into Residence
values('R8', 1940);

insert into Residence
values('R9', 1950);

insert into Residence
values('R10', 1960);


insert into Manages
values('R1', 'FDMailing1');

insert into Manages
values('R2', 'FDMailing2');

insert into Manages
values('R3', 'FDMailing3');

insert into Manages
values('R4', 'FDMailing4');

insert into Manages
values('R5', 'FDMailing5');

insert into Has
values('DH1', 'R1');

insert into Has
values('DH2', 'R2');

insert into Has
values('DH3', 'R3');

insert into Has
values('DH4', 'R4');

insert into Has
values('DH5', 'R5');

insert into Grouped
values('B1Address', 'B1', 'R1');

insert into Grouped
values('B2Address', 'B2', 'R2');

insert into Grouped
values('B3Address', 'B3', 'R3');

insert into Grouped
values('B4Address', 'B4', 'R4');

insert into Grouped
values('B5Address', 'B5', 'R5');

insert into Room
values('B1Address', 'B1', 10, 'Studio');

insert into Room
values('B2Address', 'B2', 20, 'Studio');

insert into Room
values('B3Address', 'B3', 30, 'Shared');

insert into Room
values('B4Address', 'B4', 40, 'Shared');

insert into Room
values('B5Address', 'B5', 50, 'Studio');

insert into Room
values('B1Address', 'B1', 60, 'Studio');

insert into Room
values('B2Address', 'B2', 70, 'Studio');

insert into Room
values('B3Address', 'B3', 80, 'Shared');

insert into Room
values('B4Address', 'B4', 90, 'Shared');

insert into Room
values('B5Address', 'B5', 100, 'Studio');

insert into Room
values('B1Address', 'B1', 110, 'Single');

insert into Room
values('B2Address', 'B2', 120, 'Single');

insert into Room
values('B3Address', 'B3', 130, 'Single');

insert into Room
values('B4Address', 'B4', 140, 'Single');

insert into Room
values('B5Address', 'B5', 150, 'Single');

insert into Room
values('B1Address', 'B1', 160, 'Double');

insert into Room
values('B2Address', 'B2', 170, 'Double');

insert into Room
values('B3Address', 'B3', 180, 'Double');

insert into Room
values('B4Address', 'B4', 190, 'Double');

insert into Room
values('B5Address', 'B5', 200, 'Double');

insert into Room
values('B1Address', 'B1', 210, 'Single');

insert into Room
values('B2Address', 'B2', 220, 'Single');

insert into Room
values('B3Address', 'B3', 230, 'Single');

insert into Room
values('B4Address', 'B4', 240, 'Single');

insert into Room
values('B5Address', 'B5', 250, 'Single');

insert into Room
values('B1Address', 'B1', 260, 'Double');

insert into Room
values('B2Address', 'B2', 270, 'Double');

insert into Room
values('B3Address', 'B3', 280, 'Double');

insert into Room
values('B4Address', 'B4', 290, 'Double');

insert into Room
values('B5Address', 'B5', 300, 'Double');

insert into Room
values('B1Address', 'B1', 310, 'Studio');

insert into Room
values('B2Address', 'B2', 320, 'Studio');

insert into Room
values('B3Address', 'B3', 330, 'Shared');

insert into Room
values('B4Address', 'B4', 340, 'Shared');

insert into Room
values('B5Address', 'B5', 350, 'Studio');

insert into Room
values('B1Address', 'B1', 360, 'Studio');

insert into Room
values('B2Address', 'B2', 370, 'Studio');

insert into Room
values('B3Address', 'B3', 380, 'Shared');

insert into Room
values('B4Address', 'B4', 390, 'Shared');

insert into Room
values('B5Address', 'B5', 400, 'Studio');

insert into Room
values('B1Address', 'B1', 410, 'Studio');

insert into Room
values('B2Address', 'B2', 420, 'Studio');

insert into Room
values('B3Address', 'B3', 430, 'Shared');

insert into Room
values('B4Address', 'B4', 440, 'Shared');

insert into Room
values('B5Address', 'B5', 450, 'Studio');

insert into Room
values('B1Address', 'B1', 460, 'Studio');

insert into Room
values('B2Address', 'B2', 470, 'Studio');

insert into Room
values('B3Address', 'B3', 480, 'Studio');

insert into Room
values('B4Address', 'B4', 490, 'Shared');

insert into Room
values('B5Address', 'B5', 500, 'Single');

insert into Room
values('B1Address', 'B1', 510, 'Studio');

insert into Room
values('B2Address', 'B2', 520, 'Single');

insert into Room
values('B3Address', 'B3', 530, 'Studio');

insert into Room
values('B4Address', 'B4', 540, 'Studio');

insert into Room
values('B5Address', 'B5', 550, 'Single');

insert into Room
values('B5Address', 'B5', 560, 'Single');

insert into Resident
values(111111111, 'N1', '123 456 1126', 'L1', 18, 'UNI1');

insert into Resident
values(222222222, 'N2', '123 456 1127', 'L2', 18, 'UNI1');

insert into Resident
values(333333333, 'N3', '123 456 1128', 'L3', 19, 'UNI1');

insert into Resident
values(444444444, 'N4', '123 456 1129', 'L4', 19, 'UNI1');

insert into Resident
values(555555555, 'N5', '123 456 1130', 'L5', 20, 'UNI1');

insert into Resident
values(111111112, 'N6', '321 456 1126', 'L1', 18, 'UNI1');

insert into Resident
values(222222223, 'N7', '321 456 1127', 'L2', 18, 'UNI1');

insert into Resident
values(333333334, 'N8', '321 456 1128', 'L3', 19, 'UNI1');

insert into Resident
values(444444445, 'N9', '321 456 1129', 'L4', 19, 'UNI1');

insert into Resident
values(555555556, 'N10', '321 456 1130', 'L5', 20, 'UNI1');

insert into Resident
values(111111113, 'N11', '123 654 1126', 'L1', 18, 'UNI1');

insert into Resident
values(222222224, 'N12', '123 654 1127', 'L2', 18, 'UNI1');

insert into Resident
values(333333335, 'N13', '123 654 1128', 'L3', 19, 'UNI1');

insert into Resident
values(444444446, 'N14', '123 654 1129', 'L4', 19, 'UNI1');

insert into Resident
values(555555557, 'N15', '123 654 1130', 'L5', 20, 'UNI1');

insert into Resident
values(111111114, 'N16', '321 654 1126', 'L1', 21, 'UNI1');

insert into Resident
values(222222225, 'N17', '321 654 1127', 'L2', 18, 'UNI1');

insert into Resident
values(333333336, 'N18', '321 654 1128', 'L3', 19, 'UNI1');

insert into Resident
values(444444447, 'N19', '321 654 1129', 'L4', 19, 'UNI1');

insert into Resident
values(555555558, 'N20', '321 654 1130', 'L5', 20, 'UNI1');

insert into Resident
values(111111115, 'N21', '213 456 1126', 'L1', 24, 'UNI1');

insert into Resident
values(222222226, 'N22', '213 456 1127', 'L2', 18, 'UNI1');

insert into Resident
values(333333337, 'N23', '213 456 1128', 'L3', 19, 'UNI1');

insert into Resident
values(444444448, 'N24', '213 456 1129', 'L4', 19, 'UNI1');

insert into Resident
values(555555559, 'N25', '213 456 1130', 'L5', 23, 'UNI1');

insert into Resident
values(111111116, 'N26', '231 456 1126', 'L1', 18, 'UNI1');

insert into Resident
values(222222227, 'N27', '231 456 1127', 'L2', 18, 'UNI1');

insert into Resident
values(333333338, 'N28', '231 456 1128', 'L3', 24, 'UNI1');

insert into Resident
values(444444449, 'N29', '231 456 1129', 'L4', 19, 'UNI1');

insert into Resident
values(555555560, 'N30', '231 456 1130', 'L5', 20, 'UNI1');

insert into Resident
values(111111117, 'N31', '123 564 1126', 'L1', 24, 'UNI1');

insert into Resident
values(222222228, 'N32', '123 564 1127', 'L2', 18, 'UNI1');

insert into Resident
values(333333339, 'N33', '123 564 1128', 'L3', 19, 'UNI1');

insert into Resident
values(444444450, 'N34', '123 564 1129', 'L4', 19, 'UNI1');

insert into Resident
values(555555561, 'N35', '123 564 1130', 'L5', 20, 'UNI1');

insert into Resident
values(111111118, 'N36', '321 564 1126', 'L1', 23, 'UNI1');

insert into Resident
values(222222229, 'N37', '321 564 1127', 'L2', 18, 'UNI1');

insert into Resident
values(333333340, 'N38', '321 564 1128', 'L3', 19, 'UNI1');

insert into Resident
values(444444451, 'N39', '321 564 1129', 'L4', 22, 'UNI1');

insert into Resident
values(555555562, 'N40', '321 564 1130', 'L5', 20, 'UNI1');

insert into Resident
values(111111119, 'N41', '678 456 1126', 'L1', 21, 'UNI1');

insert into Resident
values(222222230, 'N42', '678 456 1127', 'L2', 18, 'UNI1');

insert into Resident
values(333333341, 'N43', '678 456 1128', 'L3', 19, 'UNI1');

insert into Resident
values(444444452, 'N44', '678 456 1129', 'L4', 19, 'UNI1');

insert into Resident
values(555555563, 'N45', '678 456 1130', 'L5', 19, 'UNI1');

insert into Resident
values(111111120, 'N46', '876 456 1126', 'L1', 23, 'UNI1');

insert into Resident
values(222222231, 'N47', '876 456 1127', 'L2', 22, 'UNI1');

insert into Resident
values(333333342, 'N48', '876 456 1128', 'L3', 19, 'UNI1');

insert into Resident
values(444444453, 'N49', '876 456 1129', 'L4', 18, 'UNI1');

insert into Resident
values(555555564, 'N50', '876 456 1130', 'L5', 24, 'UNI1');

insert into Resident
values(859555564, 'N51', '876 456 1131', 'L1', 23, 'UNI1');

insert into Resident
values(859555565, 'N52', '876 456 1132', 'L1', 23, 'UNI1');

insert into Resident
values(859555566, 'N53', '876 456 1133', 'L2', 24, 'UNI1');

insert into ResidenceAdvisor
values(666666666, 'RA1', '123 456 1131', 900000016, 'UNI1', 22);

insert into ResidenceAdvisor
values(777777777, 'RA2', '123 456 1132', 900000017, 'UNI1', 21);

insert into ResidenceAdvisor
values(888888888, 'RA3', '123 456 1133', 900000018, 'UNI1', 20);

insert into ResidenceAdvisor
values(999999999, 'RA4', '123 456 1134', 900000019, 'UNI1', 19);

insert into ResidenceAdvisor
values(100100100, 'RA5', '123 456 1135', 900000020, 'UNI1', 18);

insert into AssignRE
values('B1Address', 10, 'B1', 111111111);

insert into AssignRE
values('B2Address', 20, 'B2', 222222222);

insert into AssignRE
values('B3Address', 30, 'B3', 333333333);

insert into AssignRE
values('B4Address', 40, 'B4', 444444444);

insert into AssignRE
values('B5Address', 50, 'B5', 555555555);

insert into AssignRE
values('B1Address', 60, 'B1', 111111112);

insert into AssignRE
values('B2Address', 70, 'B2', 222222223);

insert into AssignRE
values('B3Address', 80, 'B3', 333333334);

insert into AssignRE
values('B4Address', 90, 'B4', 444444445);

insert into AssignRE
values('B5Address', 100, 'B5', 555555556);

insert into AssignRE
values('B1Address', 110, 'B1', 111111113);

insert into AssignRE
values('B2Address', 120, 'B2', 222222224);

insert into AssignRE
values('B3Address', 130, 'B3', 333333335);

insert into AssignRE
values('B4Address', 140, 'B4', 444444446);

insert into AssignRE
values('B5Address', 150, 'B5', 555555557);

insert into AssignRE
values('B1Address', 160, 'B1', 111111114);

insert into AssignRE
values('B1Address', 160, 'B1', 859555564);

insert into AssignRE
values('B2Address', 170, 'B2', 222222225);

insert into AssignRE
values('B3Address', 180, 'B3', 333333336);

insert into AssignRE
values('B4Address', 190, 'B4', 444444447);

insert into AssignRE
values('B5Address', 200, 'B5', 555555558);

insert into AssignRE
values('B1Address', 210, 'B1', 111111115);

insert into AssignRE
values('B2Address', 220, 'B2', 222222226);

insert into AssignRE
values('B3Address', 230, 'B3', 333333337);

insert into AssignRE
values('B4Address', 240, 'B4', 444444448);

insert into AssignRE
values('B5Address', 250, 'B5', 555555559);

insert into AssignRE
values('B1Address', 260, 'B1', 111111116);

insert into AssignRE
values('B2Address', 270, 'B2', 222222227);

insert into AssignRE
values('B3Address', 280, 'B3', 333333338);

insert into AssignRE
values('B4Address', 290, 'B4', 444444449);

insert into AssignRE
values('B5Address', 300, 'B5', 555555560);

insert into AssignRE
values('B1Address', 310, 'B1', 111111117);

insert into AssignRE
values('B2Address', 320, 'B2', 222222228);

insert into AssignRE
values('B3Address', 330, 'B3', 333333339);

insert into AssignRE
values('B4Address', 340, 'B4', 444444450);

insert into AssignRE
values('B5Address', 350, 'B5', 555555561);

insert into AssignRE
values('B1Address', 360, 'B1', 111111118);

insert into AssignRE
values('B2Address', 370, 'B2', 222222229);

insert into AssignRE
values('B3Address', 380, 'B3', 333333340);

insert into AssignRE
values('B4Address', 390, 'B4', 444444451);

insert into AssignRE
values('B5Address', 400, 'B5', 555555562);

insert into AssignRE
values('B1Address', 410, 'B1', 111111119);

insert into AssignRE
values('B2Address', 420, 'B2', 222222230);

insert into AssignRE
values('B3Address', 430, 'B3', 333333341);

insert into AssignRE
values('B4Address', 440, 'B4', 444444452);

insert into AssignRE
values('B5Address', 450, 'B5', 555555563);

insert into AssignRE
values('B1Address', 460, 'B1', 111111120);

insert into AssignRE
values('B2Address', 470, 'B2', 222222231);

insert into AssignRE
values('B3Address', 480, 'B3', 333333342);

insert into AssignRE
values('B4Address', 490, 'B4', 444444453);

insert into AssignRE
values('B5Address', 500, 'B5', 555555564);

insert into AssignRA
values('B1Address', 510, 'B1', 666666666);

insert into AssignRA
values('B2Address', 520, 'B2', 777777777);

insert into AssignRA
values('B3Address', 530, 'B3', 888888888);

insert into AssignRA
values('B4Address', 540, 'B4', 999999999);

insert into AssignRA
values('B5Address', 550, 'B5', 100100100);

insert into CleaningStaff
values(900000006, 14.60, 'CS1', '123 456 1116', 'UNI1');

insert into CleaningStaff
values(900000007, 17.30, 'CS2', '123 456 1117', 'UNI1');

insert into CleaningStaff
values(900000008, 18.10, 'CS3', '123 456 1118', 'UNI1');

insert into CleaningStaff
values(900000009, 14.60, 'CS4', '123 456 1119', 'UNI1');

insert into CleaningStaff
values(900000010, 15.75, 'CS5', '123 456 1120', 'UNI1');

insert into CleaningStaff
values(910000006, 19.60, 'CS6', '321 456 1116', 'UNI1');

insert into CleaningStaff
values(920000007, 15.90, 'CS7', '321 456 1117', 'UNI1');

insert into CleaningStaff
values(930000008, 16.55, 'CS8', '321 456 1118', 'UNI1');

insert into CleaningStaff
values(940000009, 18.95, 'CS9', '321 456 1119', 'UNI1');

insert into CleaningStaff
values(950000010, 15.55, 'CS10', '321 456 1120', 'UNI1');

insert into CleaningStaff
values(960000006, 17.60, 'CS11', '213 456 1116', 'UNI1');

insert into CleaningStaff
values(970000007, 18.35, 'CS12', '213 456 1117', 'UNI1');

insert into CleaningStaff
values(980000008, 14.15, 'CS13', '213 456 1118', 'UNI1');

insert into CleaningStaff
values(990000009, 19.75, 'CS14', '213 456 1119', 'UNI1');

insert into CleaningStaff
values(910000010, 14.60, 'CS15', '213 456 1120', 'UNI1');

insert into CleaningStaff
values(911000006, 19.75, 'CS16', '312 456 1116', 'UNI1');

insert into CleaningStaff
values(912000007, 25.34, 'CS17', '312 456 1117', 'UNI1');

insert into CleaningStaff
values(913000008, 23.50, 'CS18', '312 456 1118', 'UNI1');

insert into CleaningStaff
values(914000009, 15.69, 'CS19', '312 456 1119', 'UNI1');

insert into CleaningStaff
values(915000010, 13.30, 'CS20', '312 456 1120', 'UNI1');

insert into CleaningStaff
values(916000006, 15.45, 'CS21', '123 654 1116', 'UNI1');

insert into CleaningStaff
values(917000007, 17.25, 'CS22', '123 654 1117', 'UNI1');

insert into CleaningStaff
values(918000008, 23.35, 'CS23', '123 654 1118', 'UNI1');

insert into CleaningStaff
values(919000009, 26.56, 'CS24', '123 654 1119', 'UNI1');

insert into CleaningStaff
values(920000010, 25.98, 'CS25', '123 654 1120', 'UNI1');

insert into CleaningStaff
values(921000006, 19.62, 'CS26', '123 546 1116', 'UNI1');

insert into CleaningStaff
values(922000007, 14.25, 'CS27', '123 546 1117', 'UNI1');

insert into CleaningStaff
values(923000008, 18.63, 'CS28', '123 546 1118', 'UNI1');

insert into CleaningStaff
values(924000009, 16.65, 'CS29', '123 546 1119', 'UNI1');

insert into CleaningStaff
values(925000010, 21.62, 'CS30', '123 546 1120', 'UNI1');

insert into DiningHallStaff
values(900000011, 19.00, 'DHS1', '123 456 1121', 'UNI1');

insert into DiningHallStaff
values(900000012, 17.20, 'DHS2', '123 456 1122', 'UNI1');

insert into DiningHallStaff
values(900000013, 18.55, 'DHS3', '123 456 1123', 'UNI1');

insert into DiningHallStaff
values(900000014, 15.30, 'DHS4', '123 456 1124', 'UNI1');

insert into DiningHallStaff
values(900000015, 14.64, 'DHS5', '123 456 1125', 'UNI1');

insert into DiningHallStaff
values(900010011, 19.26, 'DHS1', '321 456 1121', 'UNI1');

insert into DiningHallStaff
values(900020012, 18.17, 'DHS2', '321 456 1122', 'UNI1');

insert into DiningHallStaff
values(900030013, 17.54, 'DHS3', '321 456 1123', 'UNI1');

insert into DiningHallStaff
values(900040014, 20.85, 'DHS4', '321 456 1124', 'UNI1');

insert into DiningHallStaff
values(900050015, 21.76, 'DHS5', '321 456 1125', 'UNI1');

insert into Cleans
values(900000006, 'B1Address', 'B1');

insert into Cleans
values(900000007, 'B2Address', 'B2');

insert into Cleans
values(900000008, 'B3Address', 'B3');

insert into Cleans
values(900000009, 'B4Address', 'B4');

insert into Cleans
values(900000010, 'B5Address', 'B5');

insert into Cleans
values(910000006, 'B1Address', 'B1');

insert into Cleans
values(920000007, 'B2Address', 'B2');

insert into Cleans
values(930000008, 'B3Address', 'B3');

insert into Cleans
values(940000009, 'B4Address', 'B4');

insert into Cleans
values(950000010, 'B5Address', 'B5');

insert into Cleans
values(960000006, 'B1Address', 'B1');

insert into Cleans
values(970000007, 'B2Address', 'B2');

insert into Cleans
values(980000008, 'B3Address', 'B3');

insert into Cleans
values(990000009, 'B4Address', 'B4');

insert into Cleans
values(910000010, 'B5Address', 'B5');

insert into Cleans
values(910000010, 'B1Address', 'B1');

insert into Cleans
values(911000006, 'B1Address', 'B1');

insert into Cleans
values(912000007, 'B2Address', 'B2');

insert into Cleans
values(912000007, 'B3Address', 'B3');

insert into Cleans
values(912000007, 'B4Address', 'B4');

insert into Cleans
values(913000008, 'B3Address', 'B3');

insert into Cleans
values(914000009, 'B4Address', 'B4');

insert into Cleans
values(914000009, 'B3Address', 'B3');

insert into Cleans
values(915000010, 'B5Address', 'B5');

insert into Cleans
values(916000006, 'B1Address', 'B1');

insert into Cleans
values(917000007, 'B2Address', 'B2');

insert into Cleans
values(918000008, 'B3Address', 'B3');

insert into Cleans
values(919000009, 'B4Address', 'B4');

insert into Cleans
values(920000010, 'B5Address', 'B5');

insert into Cleans
values(921000006, 'B1Address', 'B1');

insert into Cleans
values(921000006, 'B2Address', 'B2');

insert into Cleans
values(921000006, 'B3Address', 'B3');

insert into Cleans
values(921000006, 'B4Address', 'B4');

insert into Cleans
values(921000006, 'B5Address', 'B5');

insert into Cleans
values(922000007, 'B1Address', 'B1');

insert into Cleans
values(922000007, 'B2Address', 'B2');

insert into Cleans
values(922000007, 'B3Address', 'B3');

insert into Cleans
values(922000007, 'B4Address', 'B4');

insert into Cleans
values(922000007, 'B5Address', 'B5');

insert into Cleans
values(923000008, 'B1Address', 'B1');

insert into Cleans
values(923000008, 'B2Address', 'B2');

insert into Cleans
values(923000008, 'B3Address', 'B3');

insert into Cleans
values(923000008, 'B4Address', 'B4');

insert into Cleans
values(923000008, 'B5Address', 'B5');

insert into Cleans
values(924000009, 'B1Address', 'B1');

insert into Cleans
values(924000009, 'B2Address', 'B2');

insert into Cleans
values(924000009, 'B3Address', 'B3');

insert into Cleans
values(924000009, 'B4Address', 'B4');

insert into Cleans
values(924000009, 'B5Address', 'B5');

insert into Cleans
values(925000010, 'B1Address', 'B1');

insert into Cleans
values(925000010, 'B2Address', 'B2');

insert into Cleans
values(925000010, 'B3Address', 'B3');

insert into Cleans
values(925000010, 'B4Address', 'B4');

insert into Cleans
values(925000010, 'B5Address', 'B5');

insert into WorksIn
values(900000011, 'DH1');

insert into WorksIn
values(900000012, 'DH2');

insert into WorksIn
values(900000013, 'DH3');

insert into WorksIn
values(900000014, 'DH4');

insert into WorksIn
values(900000015, 'DH5');

insert into WorksIn
values(900010011, 'DH1');

insert into WorksIn
values(900020012, 'DH2');

insert into WorksIn
values(900030013, 'DH3');

insert into WorksIn
values(900040014, 'DH4');

insert into WorksIn
values(900050015, 'DH5');

insert into LivesInRE
values('R1', 111111111);

insert into LivesInRE
values('R2', 222222222);

insert into LivesInRE
values('R3', 333333333);

insert into LivesInRE
values('R4', 444444444);

insert into LivesInRE
values('R5', 555555555);

insert into LivesInRE
values('R1', 111111112);

insert into LivesInRE
values('R2', 222222223);

insert into LivesInRE
values('R3', 333333334);

insert into LivesInRE
values('R4', 444444445);

insert into LivesInRE
values('R5', 555555556);

insert into LivesInRE
values('R1', 111111113);

insert into LivesInRE
values('R2', 222222224);

insert into LivesInRE
values('R3', 333333335);

insert into LivesInRE
values('R4', 444444446);

insert into LivesInRE
values('R5', 555555557);

insert into LivesInRE
values('R1', 111111114);

insert into LivesInRE
values('R2', 222222225);

insert into LivesInRE
values('R3', 333333336);

insert into LivesInRE
values('R4', 444444447);

insert into LivesInRE
values('R5', 555555558);

insert into LivesInRE
values('R1', 111111115);

insert into LivesInRE
values('R2', 222222226);

insert into LivesInRE
values('R3', 333333337);

insert into LivesInRE
values('R4', 444444448);

insert into LivesInRE
values('R5', 555555559);

insert into LivesInRE
values('R1', 111111116);

insert into LivesInRE
values('R2', 222222227);

insert into LivesInRE
values('R3', 333333338);

insert into LivesInRE
values('R4', 444444449);

insert into LivesInRE
values('R5', 555555560);

insert into LivesInRE
values('R1', 111111117);

insert into LivesInRE
values('R2', 222222228);

insert into LivesInRE
values('R3', 333333339);

insert into LivesInRE
values('R4', 444444450);

insert into LivesInRE
values('R5', 555555561);

insert into LivesInRE
values('R1', 111111118);

insert into LivesInRE
values('R2', 222222229);

insert into LivesInRE
values('R3', 333333340);

insert into LivesInRE
values('R4', 444444451);

insert into LivesInRE
values('R5', 555555562);

insert into LivesInRE
values('R1', 111111119);

insert into LivesInRE
values('R2', 222222230);

insert into LivesInRE
values('R3', 333333341);

insert into LivesInRE
values('R4', 444444452);

insert into LivesInRE
values('R5', 555555563);

insert into LivesInRE
values('R1', 111111120);

insert into LivesInRE
values('R2', 222222231);

insert into LivesInRE
values('R3', 333333342);

insert into LivesInRE
values('R4', 444444453);

insert into LivesInRE
values('R5', 555555564);

insert into LivesInRE
values('R1', 859555564);

insert into LivesInRA
values('R1', 666666666);

insert into LivesInRA
values('R2', 777777777);

insert into LivesInRA
values('R3', 888888888);

insert into LivesInRA
values('R4', 999999999);

insert into LivesInRA
values('R5', 100100100);


grant select on ResidentManagementOffice to public;
grant select on Building to public;
grant select on DiningHall to public;
grant select on FrontDeskStaff to public;
grant select on FrontDesk to public;
grant select on Residence to public;
grant select on Manages to public;
grant select on Has to public;
grant select on Grouped to public;
grant select on Room to public;
grant select on Resident to public;
grant select on ResidenceAdvisor to public;
grant select on AssignRA to public;
grant select on AssignRE to public;
grant select on CleaningStaff to public;
grant select on DiningHallStaff to public;
grant select on Cleans to public;
grant select on WorksIn to public;
grant select on LivesInRA to public;
grant select on LivesInRE to public;
