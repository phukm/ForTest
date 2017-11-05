INSERT INTO Organization (OrganizationNo,OrganizationCode,OrgNameKanji,OrgNameKana,CityId,CityCode,TelNo,Fax,Passcode) VALUES
('13194600','01','テスト団体1471','ﾃｽﾄﾀﾞﾝﾀｲ1471',(select id from City where CityName='千葉県'),(select CityCode from City where CityName='千葉県'),'04-7152-0842','04-7155-1086','661983'),
('13328100','03','テスト団体1596','ﾃｽﾄﾀﾞﾝﾀｲ1596',(select id from City where CityName='東京都'),(select CityCode from City where CityName='東京都'),'03-3263-3011','03-3265-8777','668782'),
('17181600','03','テスト団体17207','ﾃｽﾄﾀﾞﾝﾀｲ17207',(select id from City where CityName='大阪府'),(select CityCode from City where CityName='大阪府'),'072-265-7561','072-262-3385','007390'),
('22701300','03','テスト団体5548','ﾃｽﾄﾀﾞﾝﾀｲ5548',(select id from City where CityName='東京都'),(select CityCode from City where CityName='東京都'),'03-3262-4161','03-3262-4160','263869'),
('22707200','03','テスト団体5554','ﾃｽﾄﾀﾞﾝﾀｲ5554',(select id from City where CityName='東京都'),(select CityCode from City where CityName='東京都'),'042-444-9110','042-498-7801','370675'),
('22791900','03','テスト団体20069','ﾃｽﾄﾀﾞﾝﾀｲ20069',(select id from City where CityName='東京都'),(select CityCode from City where CityName='東京都'),'03-3816-6213','03-3816-6215','426891'),
('22803600','05','テスト団体20080','ﾃｽﾄﾀﾞﾝﾀｲ20080',(select id from City where CityName='東京都'),(select CityCode from City where CityName='東京都'),'03-3814-5277','03-3814-5278','156753'),
('22894000','05','テスト団体20335','ﾃｽﾄﾀﾞﾝﾀｲ20335',(select id from City where CityName='東京都'),(select CityCode from City where CityName='東京都'),'03-3762-7336','03-3766-0314','386876'),
('23070700','03','テスト団体20879','ﾃｽﾄﾀﾞﾝﾀｲ20879',(select id from City where CityName='東京都'),(select CityCode from City where CityName='東京都'),'03-5994-0721','03-5994-0724','462925');