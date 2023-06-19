<?php 
    $userRoles = [];
    $roles = \Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
    if(!empty($roles))
    {
        foreach($roles as $role)
        {
            $userRoles[] = $role->name;
        }
    }
    
?>
<aside class="main-sidebar">

    <section class="sidebar">

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
                'items' => [
                    ['label' => 'MAIN MENU', 'options' => ['class' => 'header']],
                    [
                        'label' => 'Travel Orders', 
                        'icon' => 'truck', 
                        'url' => '#',
                        'visible' => !Yii::$app->user->isGuest && (in_array('Staff', $userRoles)), 
                        'items' => [
                            ['label' => 'Add New', 'icon' => 'plus', 'url' => ['/v1/travel-order/create'], 'visible' => !Yii::$app->user->isGuest],
                            ['label' => 'List', 'icon' => 'list', 'url' => ['/v1/travel-order/'], 'visible' => !Yii::$app->user->isGuest],
                        ],
                    ],
                    
                    ['label' => 'Signatories', 'icon' => 'users', 'url' => ['/v1/signatory'], 'visible' => !Yii::$app->user->isGuest && (in_array('Administrator', $userRoles))],
                    ['label' => 'User Management', 'icon' => 'users', 'url' => ['/user/admin'], 'visible' => !Yii::$app->user->isGuest && (in_array('Administrator', $userRoles))],
                ],
            ]
        ) ?>

    </section>

</aside>
