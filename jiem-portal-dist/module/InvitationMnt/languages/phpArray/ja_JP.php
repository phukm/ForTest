<?php
return [
    'MSG404' => '結果が見つかりません。(E404)', // No result is found. - EikenScheduleID khong ton tai.
    'MSG001' => '必須入力項目です。', // This field is required.
    'MSG011' => '日付の形式はYYYY/MM/DDとしてください。', // Date format is (YYYY/MM/DD).
    'MSG013' => '検索条件に一致するデータがありません。', // No result is found.
    'MSG029' => "%u年度第%u回の申込期間外です。", // Application time of <<年度>>年度第<<回>> da het hoac chua den han.
    'MSG029-1' => "%u年度の申込期間外です。", // Application time of <<年度>>年度 da het hoac chua den han.
    'MSG030' => "%u年度第%u回が既に存在しています。", // Setting cho <<年度>>年度第<<回>> da ton tai.
    'MSG031' => '%u年度の基準級が設定されていません。', // Chua co Standard Level cho <<年度>>年度. Hay thiet lap Standard Level truoc.
    'MSG031-1' => '%u年度第%u回の基準級がまだ設定していません。設定してくさい。',
    'MSG032' => '%u年度第%u回の受験案内状が設定されていません。', // Chua co Invitation Setting cho <<年度>>年度第<<回>>. Hay thiet lap Invitation Setting truoc.
    'MSG035' => '年度・回を指定して検索してください。',
    'MSG037' => "目標級を更新する対象者を選択してください。", // There are no data to update
    'MSG038' => "申込可能級を選択してください。", // At least one level must be selected
    'MSG039' => "過年度の基準級は設定できません。", // Khong the tao Standard Level cho nhung nam trong qua khu
    'MSG041' => "ユーザID、またはパスワードが違います。", // ユーザID orパスワード is not correct. Please try again.
                                        // phan sau k co trong file excel
    'MSG045' => '団体情報の参照時にエラーが発生しました。システム管理者に連絡してください。', // System cannot show the organization information at the moment. Please view it later.
    'MSG056' => "少なくとも一つの項目を選択する必要があります。", // At least one item must be selected.
    'MSG057' => "指定した申込期限が英検の申込期限を越えています。", // 申込期限 must be less than Application End Date of selected Year and Kai.
    'MSG053' => "選択した年度には学年が登録されていません。", // There are no School Years added to selected Year.
    'MSG060' => '受験案内状の作成。', // Print invitation letter
    'MSG061' => '受験案内状の作成処理を実行しています。この処理には30分程度かかることがあります。受験案内状の作成処理が完了しましたら以下の登録されているメールアドレスに完了連絡が送付されます。<br/>%s',
    'MSG062' => '%u年度が既に存在しています。', // 年度 da ton tai.
    'MSG073' => '受験案内状を作成中のため表示できません。作成が完了するまでお待ちください。', // Inviation Letter is being generated. You cannot update data  until process is finished. 
    'Msg_No_Invitation_Letter_To_Download' => '受験案内状がありません。',
    'Msg_Please_Recommend_Level_For_Pupil' => '事前に目標級設定を行なってください。',
    'mes_title1' => '：%u年度第%u回',
    //'mes_title2' => '目標級設定',
    'MSG089' =>"氏名（カナ）はカタカナの文字です。",
    'MSG092' =>"案内状を作成しますか。",
    'MSG091'=>"目標級を設定しますか。",
    'MSG092_Empty_Pupil_For_Gen_Invitation' => '受験案内状を作成するのに生徒がいません。生徒名簿に生徒を追加してください。',
    
    // Translation in view
    'INVMNT_Year' => '年度',
    'INVMNT_Kai' => '回',
    'INVMNT_SchoolYear' => '学年',
    'INVMNT_Class' => 'クラス',
    'INVMNT_Search' => '検索',
    'INVMNT_GenInvitation' => '案内状の作成',
    'INVMNT_GenInvNotice' => '先生からのメッセージを個別に変えたい場合は、「生徒別編集」ボタンを押してください。<br/>
                                                                                全員の受験案内状の設定が済みましたら、「案内状の作成」ボタンを押してください。<br/>
                                                                                受験案内状の作成には最大で約30分ほどかかります。受験案内状の作成が完了しましたら、ご登録のメールアドレス宛に完了連絡が送付されます。完了連絡を受信しましたら、こちらの「受験案内状作成」ページに再訪し、「印刷」ボタンを押すとクラス別の受験案内状がダウンロードできます。印刷して生徒に配布ください。',
    // Cross editing
    'CONFLICTEDIT01' => '編集している内容が既に変更されます。内容を上書きしますか？「OK」を押下すると、保存します。または「キャンセル」ボタンを押下すると、変更情報をロードされます。',
    'CONFLICTDELETE01' => '変更情報が削除され、存在しないです。',
    'SGHMSG48' => '２〜５級の本会場受験には、通常料金が適用されます。よろしいですか？',
    // Payment method in view
    'YES' => 'はい',
    'NO' => 'いいえ',
    'PublicFunding' => '公費',
    'msgAllowChangeTestSite' => '試験会場区分を変更すると、受験料や申込締切日が変わる可能性があります。必ず受験案内状を作成し直し、生徒に配布してください。', //R4_MSG30

//    'msgConfirmChangeTestSite' => 'この設定変更を行う場合には注意が必要です。御団体ではすでに受験案内状が作成された履歴があるため、その情報を元に支払いが行われた可能性があります。そのため、この設定変更後は、各申し込み者に対して表示される支払い情報（金額）が異なる可能性があります。</br></br>申し込み者のお支払い状況を確認した上で、問題がないようでしたら変更を続けてください。',
    'msgConfirmChangeTestSite' => 'この設定変更を行う場合には注意が必要です。御団体ではすでに受験案内状が作成された履歴があるため、その情報を元に支払いが行われた可能性があります。</br></br>申し込み者のお支払い状況を確認した上で、問題がないようでしたら変更を続けてください。',
    'msgConfirmChangeTestSiteHallType' => 'この設定変更を行う場合には注意が必要です。御団体ではすでに受験案内状が作成された履歴があるため、その情報を元に支払いが行われた可能性があります。そのため、この設定変更後は、各申し込み者に対して表示される支払い情報（金額）が異なる可能性があります。</br></br>申し込み者のお支払い状況を確認した上で、問題がないようでしたら変更を続けてください。',
    'msgNotAllowChangeTestSite' => '既に受験料を支払った生徒がいる可能性があるため、現在試験会場区分を変更出来ません。試験会場区分を変更される場合は、英検サービスセンターにお問い合わせください。', //R4_MSG31
    'btnContinueChange' => '変更を続ける',
    'PublicFundingUse' => '公費適用',
    'PublicFundingNotUse' => '公費不適用',
    'PaymentMethod' => '支払（英検から<br/>の請求書送付）', 
    'PaymentWithBill' => '希望する', 
    'PaymentWithoutBill' => '希望しない', 
    'MSG_Allow' => '団体支払の設定から個別支払に設定を変更します。すでに生徒に向けた受験案内状を作成した履歴があります。支払いの指示などが変わるため、再度受験案内状を作成し直し、生徒に案内を配布して注意を促してください。',
    'MSG_ContactEiken' => '現在、支払形式の変更は出来ません。
受験案内状が既に作成された状態のため、既に生徒が個別に受験料の支払を行った可能性があり、事前確認が必要です。
英検サービスセンターにお問い合わせください。',
    'R4_MSG10' => '受験案内状発行日付が過去になっています。このまま続けますか？', //Issue Date must be equal to or greater than current date.
    'R4_MSG11' => '受験案内状発行日付が英検の申込期限よりも後の日付です。', //Issue date must be less than Application End Date of selected Year and Kai.
    'R4_MSG_when_refund_status_equal_2_warning_collective_payment' => '現在「本会場運営費・準会場実施経費の取扱い」について、「受験料総額から経費（準会場経費または本会場運営費）を差し引いて支払う」のオプションが選択されています。このため、個人支払いオプションを選択することができません。個人支払いを選択する場合は、まず上記のオプション選択を変更してください。',
    'DESCRIPTION_EXPIRED_PAYMENT_DATE' => '個人支払を利用する場合、%s(コンビニ)または%s(クレジット)以前を指定してください。<br>団体支払の場合は英検申込締切日までの任意の日付を設定してください。',
    'NAME_FILE_ACTIVITY_LOG' => 'アクセス_操作ログ_%s',
    'ExcelLogName' => '申込確定後の変更情報_%s',
    'Beneficiary' => '受験案内状の料金表示',
    'Beneficiary_Unuse' => '適用しない' ,
    'Beneficiary_Dantai' => '本会場料金' ,
    'Beneficiary_Student' => '準会場料金' ,
    'MSGChangeBeneficiary' => '御団体ではすでに受験案内状が作成された履歴があるため、受益者を「生徒」に変更しても、生徒は正規の申込み料金を支払います。差額分の返金は後ほど、御団体宛てにお支払いいたします。',
    'Description_OF_Beneficiary' => '（本会場料金を選択した場合）<br>
コンビニまたはクレジット支払いの場合、差額を後日返金させていただきます。<br>
団体支払の場合は、準会場料金でお支払いください。',
    'update_setting_car_before_gen_letter' => '受験案内状設定の「受験案内状の料金表示」欄の値が設定されていない為、受験案内状の生成は出来なくなりました。大変お手数ですが、受験案内状設定から、該当項目の設定を行い、再度、受験案内状の作成するようお願いします。',
    'DescChoiceKyu' => '※受験案内状作成後の、選択級の変更はできません。受験の可能性がある級は、全て選択しておいてください。',
    'okConfirmGenarateEx' => '案内状を作成する',
    'cancelConfirmGenarateEx' => '選択級を見直す',
    'MSGConfirmGenarateEx' => '案内状の作成後は、選択級の変更ができなくなります。受験の可能性がある級は、全て選択しておいてください。',
    'MSGPopupWaringGradeClassECSetting' => '学年名またはクラス名が、受験票に記載されない文字（または桁数）で入力されています。このまま案内状を作成しますか？ （記載が必要な場合は<a href="%s" target="_blank"><u>＜受験票に記載できる学年名とクラス名のルール＞</u></a>に沿って設定をしてください）',
    'OverWriteTargetKyu' => '個別に目標級を設定していない生徒の目標級のみ自動で設定します。<br>（設定済みの目標級は上書きされません。）'
];


