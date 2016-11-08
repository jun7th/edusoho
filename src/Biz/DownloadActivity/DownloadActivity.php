<?php
/**
 * User: Edusoho V8
 * Date: 02/11/2016
 * Time: 19:54
 */

namespace Biz\DownloadActivity;


use Biz\Activity\Config\Activity;
use Topxia\Common\ArrayToolkit;

class DownloadActivity extends Activity
{
    public function registerActions()
    {
        return array(
            'create' => 'WebBundle:DownLoadActivity:create',
            'edit'   => 'WebBundle:DownLoadActivity:edit',
            'show'   => 'WebBundle:DownLoadActivity:show',
        );
    }

    protected function registerListeners()
    {
        // TODO: Implement registerListeners() method.
    }

    public function getMetas()
    {
        return array(
            'name' => '下载资料',
            'icon' => 'es-icon es-icon-filedownload'
        );
    }

    /**
     * @inheritdoc
     */
    public function create($fields)
    {
        $materials = json_decode($fields['materials'], true);
        $that      = $this;
        $ext       = $this->getConnection()->transactional(function () use ($materials, $that) {
            //1. created ext
            $downloadActivity = $this->parseDownloadExtension($materials);
            $downloadActivity = $this->getDownloadActivityDao()->create($downloadActivity);
            //2. created file
            $files = $this->parseDownloadFiles($downloadActivity['id'], $materials);
            foreach ($files as $file) {
                $that->getDownloadFileDao()->create($file);
            }
            return $downloadActivity;
        });
        return $ext;
    }

    /**
     * @inheritdoc
     */
    public function update($id, $fields)
    {
        $materials = json_decode($fields['materials'], true);

        $existMaterials = $this->getDownloadFileDao()->findByDownloadActivityId($id);
        $existMaterials = ArrayToolkit::index($existMaterials, 'indicate');

        $downloadActivity = $this->getDownloadActivityDao()->get($id);

        $that = $this;

        $files = $this->parseDownloadFiles($id, $materials);

        $dropMaterials   = array_diff_key($existMaterials, $files);
        $addMaterials    = array_diff_key($files, $existMaterials);
        $updateMaterials = array_intersect_key($existMaterials, $files);


        $this->getConnection()->transactional(function () use ($id, $dropMaterials, $addMaterials, $updateMaterials, $that) {

            foreach ($dropMaterials as $material) {
                $that->getDownloadFileDao()->delete($material['id']);
            }

            foreach ($addMaterials as $material) {
                $that->getDownloadFileDao()->create($material);
            }

            foreach ($updateMaterials as $material) {
                $that->getDownloadFileDao()->update($material['id'], $material);
            }
        });
        return $downloadActivity;
    }


    /**
     * @inheritdoc
     */
    public function delete($id)
    {
        return $this->getDownloadActivityDao()->delete($id);
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        $downloadActivity              = $this->getDownloadActivityDao()->get($id);
        $downloadActivity['materials'] = $this->getDownloadFileDao()->findByActivityId($downloadActivity['id']);
        return $downloadActivity;
    }

    protected function parseDownloadExtension($materials)
    {
        return array('mediaCount' => count($materials));
    }

    protected function parseDownloadFiles($downloadActivityId, $materials)
    {
        $files = array();
        array_walk($materials, function ($material) use ($downloadActivityId, &$files) {
            $file = array(
                'downloadActivityId' => $downloadActivityId,
                'title'      => $material['name'],
                'fileId'     => intval($material['id']),
                'fileSize'   => $material['size'],
                'indicate'   => intval($material['id']),
            );
            if (intval($material['id']) == 0) {
                $file['link']     = $material['link'];
                $file['indicate'] = $file['link'];
            }
            $files[$file['indicate']] = $file;
        });
        return $files;
    }

    protected function getDownloadActivityDao()
    {
        return $this->getBiz()->dao('DownloadActivity:DownloadActivityDao');
    }

    protected function getDownloadFileDao()
    {
        return $this->getBiz()->dao('DownloadActivity:DownloadFileDao');
    }

    protected function getConnection()
    {
        return $this->getBiz()->offsetGet('db');
    }


}