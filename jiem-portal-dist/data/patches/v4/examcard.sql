Update ConditionMessages SET Messages = '次回の検定では2級に挑戦しましょう！2級を取得するためには幅広い英語力が試されます。各分野をバランス良く勉強して試験に臨みましょう。無償で利用出来るスタディギアも合格率アップにつながる内容となっています。' WHERE EikenLevelId = 3 AND Type = 1 AND `Condition` = 'condition1';

Update ConditionMessages SET Messages = '前回の結果を踏まえ次回は2級に挑戦しましょう！2級を取得するためにはこれまでより幅広い英語力が試されます。各分野をバランス良く勉強して試験に臨みましょう。無償で利用出来るスタディギアも合格率アップにつながる内容となっています。' WHERE EikenLevelId = 3 AND Type = 1 AND `Condition` = 'condition2';

Update ConditionMessages SET Messages = '前回の結果を踏まえ次回は再度2級に挑戦しましょう！次回は前回の試験で得点が伸びなかった分野を確認し、その対策をしっかりと立てた上で二次試験の準備も行いましょう！無償で利用出来るスタディギアも合格率アップにつながる内容となっています。' WHERE EikenLevelId = 3 AND Type = 1 AND `Condition` = 'condition3';


Update ConditionMessages SET Messages = '次回の検定では準2級に挑戦しましょう！準2級を取得するためには幅広い英語力が試されます。各分野をバランス良く勉強して試験に臨みましょう。無償で利用出来るスタディギアも合格率アップにつながる内容となっています。' WHERE EikenLevelId = 4 AND Type = 1 AND `Condition` = 'condition1';

Update ConditionMessages SET Messages = '前回の結果を踏まえ次回は準2級に挑戦しましょう！準2級を取得するためにはこれまでより幅広い英語力が試されます。各分野をバランス良く勉強して試験に臨みましょう。無償で利用出来るスタディギアも合格率アップにつながる内容となっています。' WHERE EikenLevelId = 4 AND Type = 1 AND `Condition` = 'condition2';

Update ConditionMessages SET Messages = '前回の結果を踏まえ次回は再度準2級に挑戦しましょう！次回は前回の試験で得点が伸びなかった分野を確認し、その対策をしっかりと立てた上で二次試験の準備も行いましょう！無償で利用出来るスタディギアも合格率アップにつながる内容となっています。' WHERE EikenLevelId = 4 AND Type = 1 AND `Condition` = 'condition3';


Update ConditionMessages SET Messages = '次回の検定では3級に挑戦しましょう！3級を取得するためには幅広い英語力が試されます。各分野をバランス良く勉強して試験に臨みましょう。無償で利用出来るスタディギアも合格率アップにつながる内容となっています。' WHERE EikenLevelId = 5 AND Type = 1 AND `Condition` = 'condition1';

Update ConditionMessages SET Messages = '前回の結果を踏まえ次回は3級に挑戦しましょう！3級を取得するためにはこれまでより幅広い英語力が試されます。各分野をバランス良く勉強して試験に臨みましょう。無償で利用出来るスタディギアも合格率アップにつながる内容となっています。' WHERE EikenLevelId = 5 AND Type = 1 AND `Condition` = 'condition2';

Update ConditionMessages SET Messages = '前回の結果を踏まえ次回は再度3級に挑戦しましょう！次回は前回の試験で得点が伸びなかった分野を確認し、その対策をしっかりと立てた上で二次試験の準備も行いましょう！無償で利用出来るスタディギアも合格率アップにつながる内容となっています' WHERE EikenLevelId = 5 AND Type = 1 AND `Condition` = 'condition3';

UPDATE ConditionMessages SET Messages = 'TIMEやNewsweekなど雑誌の社会的、経済的、文化的な記事を理解することができる' WHERE Messages = 'TIMEやNewsWeekなど雑誌の社会的、経済的、文化的な記事を理解することができる';

UPDATE Combini SET Step2 = 'お客様の受付番号（入力番号①を入力）' WHERE id = 3;