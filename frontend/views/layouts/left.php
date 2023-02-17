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

        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <p style="color: white;">Howdy, <?= Yii::$app->user->identity->username ?>!</p>

                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
            <div class="pull-left info">
                
            </div>
        </div>

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
                'items' => [
                    ['label' => 'MAIN MENU', 'options' => ['class' => 'header']],
                    ['label' => 'Dashboard', 'icon' => 'bar-chart-o', 'url' => ['/v1/dashboard']],
                    ['label' => 'Procurement Planning', 'options' => ['class' => 'header']],
                    ['label' => 'NEP', 'icon' => 'folder', 'url' => ['/v1/nep'], 'visible' => !Yii::$app->user->isGuest && (in_array('AccountingStaff', $userRoles) || in_array('Administrator', $userRoles))],
                    ['label' => 'GAA', 'icon' => 'folder', 'url' => ['/v1/gaa'], 'visible' => !Yii::$app->user->isGuest && (in_array('AccountingStaff', $userRoles) || in_array('Administrator', $userRoles))],
                    ['label' => 'PPMP', 'icon' => 'folder', 'url' => ['/v1/ppmp']],
                    ['label' => 'APP', 'icon' => 'folder', 'url' => ['/v1/app']],
                    [
                        'label' => 'Reports', 
                        'icon' => 'folder', 
                        'url' => '#', 
                        'items' => [
                            [
                                'label' => 'Monitoring', 
                                'icon' => 'folder', 
                                'url' => '#', 
                                'items' => [
                                    ['label' => 'Appropriation', 'icon' => 'folder', 'url' => ['/v1/appropriation'], 'visible' => !Yii::$app->user->isGuest && (in_array('AccountingStaff', $userRoles) || in_array('Administrator', $userRoles))],
                                    ['label' => 'Budget', 'icon' => 'folder', 'url' => ['/v1/budget-monitoring'], 'visible' => !Yii::$app->user->isGuest && (in_array('AccountingStaff', $userRoles) || in_array('Administrator', $userRoles))],
                                    ['label' => 'PPMP', 'icon' => 'folder', 'url' => ['/v1/ppmp-monitoring'], 'visible' => !Yii::$app->user->isGuest],
                            ],
                        ],
        
                    ],],
                    ['label' => 'Actual Procurement', 'options' => ['class' => 'header']],
                    ['label' => 'RIS', 'icon' => 'folder', 'url' => ['/v1/ris']],
                    ['label' => 'PR', 'icon' => 'folder', 'url' => ['/v1/pr'], 'visible' => !Yii::$app->user->isGuest && (in_array('ProcurementStaff', $userRoles) || in_array('Administrator', $userRoles))],
                    ['label' => 'APR', 'icon' => 'folder', 'url' => ['/v1/apr'], 'visible' => !Yii::$app->user->isGuest && (in_array('ProcurementStaff', $userRoles) || in_array('Administrator', $userRoles))],
                    ['label' => 'RFQ', 'icon' => 'folder', 'url' => ['/v1/rfq'], 'visible' => !Yii::$app->user->isGuest && (in_array('ProcurementStaff', $userRoles) || in_array('Administrator', $userRoles))],
                    ['label' => 'AOQ', 'icon' => 'folder', 'url' => ['/v1/aoq'], 'visible' => !Yii::$app->user->isGuest && (in_array('ProcurementStaff', $userRoles) || in_array('Administrator', $userRoles))],
                    ['label' => 'NOA', 'icon' => 'folder', 'url' => ['/v1/noa'], 'visible' => !Yii::$app->user->isGuest && (in_array('ProcurementStaff', $userRoles) || in_array('Administrator', $userRoles))],
                    ['label' => 'PO/Contracts', 'icon' => 'folder', 'url' => ['/v1/po'], 'visible' => !Yii::$app->user->isGuest && (in_array('ProcurementStaff', $userRoles) || in_array('Administrator', $userRoles))],
                    ['label' => 'NTP', 'icon' => 'folder', 'url' => ['/v1/ntp'], 'visible' => !Yii::$app->user->isGuest && (in_array('ProcurementStaff', $userRoles) || in_array('Administrator', $userRoles))],
                    ['label' => 'Inventory', 'options' => ['class' => 'header'], 'visible' => !Yii::$app->user->isGuest && (in_array('SupplyStaff', $userRoles) || in_array('Administrator', $userRoles))],
                    ['label' => 'Items', 'icon' => 'folder', 'url' => ['/v1/item'], 'visible' => !Yii::$app->user->isGuest && (in_array('SupplyStaff', $userRoles) || in_array('Administrator', $userRoles))], 
                    ['label' => 'For Contract Items', 'icon' => 'folder', 'url' => ['/v1/for-contract-item'], 'visible' => !Yii::$app->user->isGuest && (in_array('ProcurementStaff', $userRoles) || in_array('Administrator', $userRoles))], 
                    ['label' => 'Administrator', 'options' => ['class' => 'header'], 'visible' => !Yii::$app->user->isGuest && (in_array('Administrator', $userRoles))],
                    [
                        'label' => 'Libraries',
                        'icon' => 'cog',
                        'url' => '#',
                        'visible' => !Yii::$app->user->isGuest && (in_array('Administrator', $userRoles)),
                        'items' => [
                            [
                                'label' => 'Activities', 
                                'icon' => 'folder', 
                                'url' => '#', 
                                'items' => [
                                    ['label' => 'Level 1', 'icon' => 'folder', 'url' => ['/v1/pap'],],
                                    ['label' => 'Level 2', 'icon' => 'folder', 'url' => ['/v1/activity'],],
                                    ['label' => 'Level 3', 'icon' => 'folder', 'url' => ['/v1/sub-activity'],],
                            ],],
                            ['label' => 'Fund Clusters', 'icon' => 'folder', 'url' => ['/v1/fund-cluster'],],
                            ['label' => 'Fund Sources', 'icon' => 'folder', 'url' => ['/v1/fund-source'],],
                            [
                                'label' => 'PREXC', 
                                'icon' => 'folder', 
                                'url' => '#',
                                'items' => [
                                    ['label' => 'Cost Structures', 'icon' => 'folder', 'url' => ['/v1/cost-structure'],],
                                    ['label' => 'Org Outcomes', 'icon' => 'folder', 'url' => ['/v1/organizational-outcome'],],
                                    ['label' => 'Programs', 'icon' => 'folder', 'url' => ['/v1/program'],],
                                    ['label' => 'Sub-Programs', 'icon' => 'folder', 'url' => ['/v1/sub-program'],],
                                    ['label' => 'Identifiers', 'icon' => 'folder', 'url' => ['/v1/identifier'],],
                                    
                                ]
                            ],
                            ['label' => 'Objects', 'icon' => 'folder', 'url' => ['/v1/obj'],],
                            ['label' => 'Procurement Modes', 'icon' => 'folder', 'url' => ['/v1/procurement-mode'],],
                            ['label' => 'Signatories', 'icon' => 'folder', 'url' => ['/v1/signatory'],],
                        ],
                        
                    ],
                    ['label' => 'BAC', 'icon' => 'users', 'url' => ['/v1/bac-member'], 'visible' => !Yii::$app->user->isGuest && (in_array('ProcurementStaff', $userRoles) || in_array('Administrator', $userRoles))], 
                    ['label' => 'Suppliers', 'icon' => 'folder', 'url' => ['/v1/supplier'], 'visible' => !Yii::$app->user->isGuest && (in_array('ProcurementStaff', $userRoles) || in_array('Administrator', $userRoles))], 
                    
                    ['label' => 'User Management', 'icon' => 'users', 'url' => ['/user/admin'], 'visible' => !Yii::$app->user->isGuest && (in_array('Administrator', $userRoles))],
                ],
            ]
        ) ?>

    </section>

</aside>
