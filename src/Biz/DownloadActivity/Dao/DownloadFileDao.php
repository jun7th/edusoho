<?php
/**
 * User: Edusoho V8
 * Date: 03/11/2016
 * Time: 15:41
 */

namespace Biz\DownloadActivity\Dao;


use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface DownloadFileDao extends GeneralDaoInterface
{
    public function findFilesByDownloadActivityId($DownloadActivityId);
}