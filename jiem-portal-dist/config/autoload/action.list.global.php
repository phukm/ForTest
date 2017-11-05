<?php
$type = array(
    'create' => '登録',
    'update' => '更新',
    'confirm' => '確認',
    'delete' => '削除',
    'export' => 'ダウンロード',
    'generate' => '案内状作成',
    'print' => '印刷',
    'login' => 'ログイン'
);
$actionsList = array(
    'eiken' => array(
        'eikenorg' => array(
            'save' => array(
                'screen' => '申込情報登録',
                'type' => $type['create']
            )
        ),
    ),
    'history' => array(
        'iba' => array(
            'exportiba' => array(
                'screen' => '団体試験結果',
                'type' => $type['export']
            ),
            'pupilachievement' => array(
                'screen' => '英検IBA試験結果一覧',
                'type' => $type['export']
            ),
            'exportibahistorypupil' => array(
                'screen' => '英検IBA履歴',
                'type' => $type['export']
            ),
        ),
        'eiken' => array(
            'exporteiken' => array(
                'screen' => '団体試験結果',
                'type' => $type['export']
            ),
            'exporteikenhistorypupil' => array(
                'screen' => '英検履歴',
                'type' => $type['export']
            ),
            'exportibahistorypupil' => array(
                'screen' => '英検IBA履歴',
                'type' => $type['export']
            ),
            'pupilachievement' => array(
                'screen' => '英検試験結果一覧',
                'type' => $type['export']
            ),
            'examhistorylist' => array(
                'screen' => '個人試験結果',
                'type' => $type['export']
            ),
        ),
        
    ),
    'basicconstruction' => array(
        'uac' => array(
            'index' => array(
                'screen' => 'ログイン・ログアウト',
                'type' => $type['login']
            ),
            'ajaxchangepassword' => array(
                'screen' => 'パスワード変更',
                'type' => $type['update']
            ),
            'ajaxchangepasswordfirst' => array(
                'screen' => 'パスワード変更',
                'type' => $type['update']
            )
        )
    ),
    'orgmnt' => array(
        'orgschoolyear' => array(
            'save' => array(
                'screen' => '学年情報登録',
                'type' => $type['create']
            ),
            'update' => array(
                'screen' => '学年情報編集',
                'type' => $type['update']
            ),
            'delete' => array(
                'screen' => '学年情報',
                'type' => $type['delete']
            )
        ),
        'class' => array(
            'save' => array(
                'screen' => 'クラス情報登録',
                'type' => $type['create']
            ),
            'update' => array(
                'screen' => 'クラス情報編集',
                'type' => $type['update']
            ),
            'delete' => array(
                'screen' => 'クラス情報',
                'type' => $type['delete']
            )
        ),
        'user' => array(
            'save' => array(
                'screen' => 'ユーザ情報登録',
                'type' => $type['create']
            ),
            'update' => array(
                'screen' => 'ユーザ情報編集',
                'type' => $type['update']
            ),
            'delete' => array(
                'screen' => 'ユーザ情報',
                'type' => $type['delete']
            )
        )
    ),
    'pupilmnt' => array(
        'pupil' => array(
            'save' => array(
                'screen' => '生徒情報登録',
                'type' => $type['create']
            ),
            'update' => array(
                'screen' => '生徒情報編集',
                'type' => $type['update']
            ),
            'delete' => array(
                'screen' => '生徒情報',
                'type' => $type['delete']
            ),
            'exporttemplate' => array(
                'screen' => '生徒情報',
                'type' => $type['export']
            ),
            'export' => array(
                'screen' => '生徒情報',
                'type' => $type['export']
            ),
            'saveimport' => array(
                'screen' => 'アップロード',
                'type' => $type['create']
            )
        ),
        'importpupil' => array(
            'savepupil' => array(
                'screen' => 'アップロード',
                'type' => $type['create']
            )
        )
    ),
    'invitationmnt' => array(
        'standard' => array(
            'save' => array(
                'screen' => '基準級登録',
                'type' => $type['create']
            ),
            'update' => array(
                'screen' => '基準級編集',
                'type' => $type['update']
            )
        ),
        'recommended' => array(
            'update' => array(
                'screen' => '目標級設定',
                'type' => $type['update']
            ),
            'simpletest' => array(
                'screen' => '目標級設定',
                'type' => $type['update']
            )
        ),
        'setting' => array(
            'save' => array(
                'screen' => '受験案内状登録',
                'type' => $type['create']
            ),
            'update' => array(
                'screen' => '受験案内状編集',
                'type' => $type['update']
            )
        ),
        'generate' => array(
            'invitationletter' => array(
                'screen' => '受験案内状作成',
                'type' => $type['generate']
            ),
            'downloadclass' => array(
                'screen' => '受験案内状作成',
                'type' => $type['print']
            ),
            'downloadpupil' => array(
                'screen' => '生徒別編集',
                'type' => $type['print']
            ),
            'update' => array(
                'screen' => 'メッセージ編集',
                'type' => $type['update']
            )
        )
    ),
    'homepage' => array(
        'homepage' => array(
            'exportpupilstocsv' => array(
                'screen' => '目標達成状況',
                'type' => $type['export']
            ),
            'getexportlistattendpupil' => array(
                'screen' => '英検受験実績',
                'type' => $type['export']
            )
        )
    ),
    'goalsetting' => array(
        'graduationgoalsetting' => array(
            'updategraduationgoal' => array(
                'screen' => '目標情報',
                'type' => $type['update']
            ),
        )
    ),
    'iba' => array(
        'iba' => array(
            'show' => array(
                'screen' => '申込情報詳細',
                'type' => $type['create']
            ),
            'savedraft' => array(
                'screen' => '申込情報登録',
                'type' => $type['create']
            )
        )
    )
);

return array(
    'actions_list' => $actionsList,
    'actions_type' => $type
);