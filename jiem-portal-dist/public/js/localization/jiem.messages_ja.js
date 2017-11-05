/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/*
 * Example
 * $.jiem_msg.eiken_msg.MSG001
 * 
 */

(function ($) {
    $.jiem_msg = function () {
       
    };
    $.extend($.jiem_msg, {
        org_msg: {
            MSG001: "このフィールドが必須です。",
            MSG014: "明確な整数値を入力してください。",
            MSG043: "99以上のアイテムを作成できません。",
            MSG046: "学年（更新名）",
            MSG999:"数ではありません",
            MSG1000:"未満250文字",
            MSG1001:"未満2文字",
            // ban chua chon
            MSG1003:"あなたが選択されていません",
            // ban muon xoa
            MSG1004:"削除対象項目を選択してください。"
        },
        pupil_msg: {
            required: "This {0} field is required."
        },
        invitation_msg:{
            MSG000: "結果が見つかりません。(E404)",
            MSG001: "このフィールドが必須です。",
            MSG011: "日付の形式がYYYY/MM/DDであります。",
            MSG013: "結果が見つかりません。",
            MSG031: "<<年度>>年度第<<回>>の申込期間にならないか、もう切れになりました。", // Application time of <<年度>>年度第<<回>> da het hoac chua den han.
            MSG032: "年度+”年度第”+回+”回”が既に存在しています。", // Setting cho <<年度>>年度第<<回>> da ton tai.
            MSG041: "級を選択してください。", // At least one level must be selected
            MSG038: "年度・回を指定し、検索してください。",
            MSG056: "少なくとも1つの項目を選択する必要があります。", // At least one item must be selected.
            MSG037: "目標級を指定してから、更新してください。", // There are no data to update
            MSG029: "%u年度第%u回の申込期間にならないか、もう切れになりました。",
            MSG057: "申込期限が選択された年度と回のアプリケーション完了日より小さく必要であります。"
        }
    });
}(jQuery));