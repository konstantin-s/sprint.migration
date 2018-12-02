<?php

namespace Sprint\Migration\Schema;

use \Sprint\Migration\AbstractSchema;
use Sprint\Migration\HelperManager;
use Sprint\Migration\Out;

class IblockSchema extends AbstractSchema
{

    public function export() {
        $helper = new HelperManager();

        $types = $helper->Iblock()->getIblockTypes();
        $exportTypes = array();
        foreach ($types as $type) {
            $exportTypes[] = $helper->Iblock()->exportIblockType($type['ID']);
        }

        $this->saveSchema('iblock_types', array(
            'items' => $exportTypes
        ));


        $iblocks = $helper->Iblock()->getIblocks();
        foreach ($iblocks as $iblock) {
            if (!empty($iblock['CODE'])) {
                $this->saveSchema('iblocks/' . $iblock['IBLOCK_TYPE_ID'] . '-' . $iblock['CODE'], array(
                    'iblock' => $helper->Iblock()->exportIblock($iblock['ID']),
                    'fields' => $helper->Iblock()->exportIblockFields($iblock['ID']),
                    'props' => $helper->Iblock()->exportProperties($iblock['ID']),
                    'element_form' => $helper->AdminIblock()->extractElementForm($iblock['ID'])
                ));
            }
        }
    }


    public function import() {
        $helper = new HelperManager();


        $schemaTypes = $this->loadSchema('iblock_types');
        $this->exitIfEmpty($schemaTypes, 'iblock types not found');

        foreach ($schemaTypes['items'] as $type) {
            $exists = $helper->Iblock()->exportIblockType($type['ID']);

            if ($exists != $type) {
                $helper->Iblock()->saveIblockType($type);
                $this->outSuccess('iblock type %s updated', $type['ID']);
            } else {
                $this->out('iblock type %s is equal', $type['ID']);
            }

        }


        $schemaIblocks = $this->loadSchemas('iblocks/');

        foreach ($schemaIblocks as $name => $schemaIblock) {

            $iblockId = $helper->Iblock()->getIblockId(
                $schemaIblock['iblock']['CODE'],
                $schemaIblock['iblock']['IBLOCK_TYPE_ID']
            );

            $exists = $helper->Iblock()->exportIblock($iblockId);
            if ($exists != $schemaIblock['iblock']) {
                $helper->Iblock()->saveIblock($schemaIblock['iblock']);
                $this->outSuccess('iblock %s:%s updated',
                    $schemaIblock['iblock']['IBLOCK_TYPE_ID'],
                    $schemaIblock['iblock']['CODE']
                );
            } else {
                $this->out('iblock %s:%s is equal',
                    $schemaIblock['iblock']['IBLOCK_TYPE_ID'],
                    $schemaIblock['iblock']['CODE']
                );
            }

            $exists = $helper->Iblock()->exportIblockFields($iblockId);
            if ($exists != $schemaIblock['fields']) {
                $helper->Iblock()->saveIblockFields($schemaIblock['fields']);
                $this->outSuccess('iblock fields %s:%s updated',
                    $schemaIblock['iblock']['IBLOCK_TYPE_ID'],
                    $schemaIblock['iblock']['CODE']
                );
            } else {
                $this->out('iblock fields %s:%s is equal',
                    $schemaIblock['iblock']['IBLOCK_TYPE_ID'],
                    $schemaIblock['iblock']['CODE']
                );
            }
        }

        foreach ($schemaIblocks as $name => $schemaIblock) {
            $iblockId = $helper->Iblock()->getIblockId(
                $schemaIblock['iblock']['CODE'],
                $schemaIblock['iblock']['IBLOCK_TYPE_ID']
            );


            $existsProps = $helper->Iblock()->exportProperties($iblockId);

            foreach ($schemaIblock['props'] as $prop) {
                $exists = $this->findByCode($prop['CODE'], $existsProps);

                if ($exists != $prop) {
                    $helper->Iblock()->saveProperty($iblockId, $prop);
                    $this->outSuccess('iblock property %s updated',
                        $prop['CODE']
                    );
                } else {
                    $this->out('iblock property %s is equal',
                        $prop['CODE']
                    );
                }
            }

            foreach ($existsProps as $existsProp) {
                if (!$this->findByCode($existsProp['CODE'], $schemaIblock['props'])) {
                    $helper->Iblock()->deletePropertyIfExists($iblockId, $existsProp['CODE']);
                    $this->outError('iblock property %s is delete',
                        $existsProp['CODE']
                    );
                }
            }


            if (!empty($schemaIblock['element_form'])) {
                $exists = $helper->AdminIblock()->extractElementForm($iblockId);
                if ($exists != $schemaIblock['element_form']) {
                    $helper->AdminIblock()->saveElementForm($iblockId, $schemaIblock['element_form']);
                    $this->outSuccess('iblock admin form %s:%s updated',
                        $schemaIblock['iblock']['IBLOCK_TYPE_ID'],
                        $schemaIblock['iblock']['CODE']
                    );
                } else {
                    $this->out('iblock admin form %s:%s is equal',
                        $schemaIblock['iblock']['IBLOCK_TYPE_ID'],
                        $schemaIblock['iblock']['CODE']
                    );
                }
            }

        }


    }

    protected function findByCode($code, $haystack) {
        foreach ($haystack as $item) {
            if ($item['CODE'] == $code) {
                return $item;
            }
        }

        return false;
    }


}