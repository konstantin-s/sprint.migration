<?php

namespace Sprint\Migration\Builders;

use Sprint\Migration\Exceptions\RebuildException;
use Sprint\Migration\Locale;
use Sprint\Migration\Module;
use Sprint\Migration\VersionBuilder;

class UserOptionsBuilder extends VersionBuilder
{

    protected function isBuilderEnabled()
    {
        return true;
    }

    protected function initialize()
    {
        $this->setTitle(Locale::getMessage('BUILDER_UserOptionsExport_Title'));
        $this->setDescription(Locale::getMessage('BUILDER_UserOptionsExport_Desc'));
        $this->addVersionFields();
    }

    /**
     * @throws RebuildException
     */
    protected function execute()
    {
        $helper = $this->getHelperManager();

        $this->addField('what', [
            'title' => Locale::getMessage('BUILDER_UserOptionsExport_What'),
            'width' => 250,
            'multiple' => 1,
            'value' => [],
            'select' => [
                [
                    'title' => Locale::getMessage('BUILDER_UserOptionsExport_WhatUserForm'),
                    'value' => 'userForm',
                ],
                [
                    'title' => Locale::getMessage('BUILDER_UserOptionsExport_WhatUserList'),
                    'value' => 'userList',
                ],
                [
                    'title' => Locale::getMessage('BUILDER_UserOptionsExport_WhatGroupList'),
                    'value' => 'groupList',
                ],
            ],
        ]);


        $what = $this->getFieldValue('what');
        if (!empty($what)) {
            $what = is_array($what) ? $what : [$what];
        } else {
            $this->rebuildField('what');
        }


        $exportUserForm = [];
        $exportUserList = [];
        $exportUserGroupList = [];
        $exportUserGrid = [];
        $exportUserGroupGrid = [];

        if (in_array('userForm', $what)) {
            $exportUserForm = $helper->UserOptions()->exportUserForm();
        }
        if (in_array('userList', $what)) {
            $exportUserList = $helper->UserOptions()->exportUserList();
            $exportUserGrid = $helper->UserOptions()->exportGrid(
                $helper->UserOptions()->getUserGridId()
            );
        }
        if (in_array('groupList', $what)) {
            $exportUserGroupList = $helper->UserOptions()->exportUserGroupList();
            $exportUserGroupGrid = $helper->UserOptions()->exportGrid(
                $helper->UserOptions()->getUserGroupGridId()
            );
        }

        $this->createVersionFile(
            Module::getModuleDir() . '/templates/UserOptionsExport.php', [
            'exportUserForm' => $exportUserForm,
            'exportUserList' => $exportUserList,
            'exportUserGroupList' => $exportUserGroupList,
            'exportUserGrid' => $exportUserGrid,
            'exportUserGroupGrid' => $exportUserGroupGrid,
        ]);

    }
}