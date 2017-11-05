SET NAMES UTF8;
TRUNCATE TABLE `NewsEiken`;
INSERT INTO `NewsEiken` (`id`, `NewsDate`, `Description`, `Url`, `Type`, `UpdateAt`, `UpdateBy`, `InsertAt`, `InsertBy`, `Status`, `IsDelete`)
VALUES
  (1, '2016-07-10 00:00:00', '4級・5級受験申込者全員が受けられるスピーキングテストが始まりました！', 'https://www.eiken.or.jp/eiken/exam/4s5s/', NULL,
      NOW(), NULL, NOW(), NULL, NULL, 0),
  (2, '2016-07-11 00:00:00', '【プレスリリース】小学生・幼稚園生向けのEラーニングアプリ「英検Jr. for dキッズ」「英検4-5級ラーニング for dキッズ」リリースのお知らせ',
      'https://www.eiken.or.jp/eiken-junior/info/2016/pdf/20160711_pressrelease_dkids.pdf', NULL, NOW(), NULL, NOW(),
      NULL, NULL, 0),
  (3, '2016-07-19 00:00:00', '2016年度からの「2級A」の認定基準につきまして', 'https://www.eiken.or.jp/eiken/info/2016/0719_01.html', NULL,
      NOW(), NULL, NOW(), NULL, NULL, 0);