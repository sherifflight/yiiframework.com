<?php

namespace app\controllers;

use app\components\packagist\Package;
use app\components\packagist\PackagistApi;
use Yii;
use yii\data\Pagination;
use yii\data\Sort;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\VarDumper;
use yii\web\Controller;

/**
 * Extensions section
 */
class ExtensionController extends Controller
{
    /**
     * Main page, package search
     *
     * @param null|string $q
     * @param null|integer $page
     * @return string
     */
    public function actionIndex($q = null, $page = null)
    {
        $pagination = null;

        $query = trim($q);
        $sort = new Sort(
            [
                'attributes' => [
                    'downloads',
                    'favers'
                ],
                'defaultOrder' => [
                    'downloads' => SORT_DESC,
                ]
            ]
        );

        $cacheKey = 'extension/index__packagistData_' . md5(serialize([$query, $page, $sort->getOrders()]));
        $packagistData = \Yii::$app->cache->get($cacheKey);
        if ($packagistData === false) {
            $packagistData = (new PackagistApi())->search($query, $page, $sort->getOrders());
            \Yii::$app->cache->set($cacheKey, $packagistData, Yii::$app->params['cache.extensions.search']);
        }

        if ($packagistData['packages']) {
            $pagination = new Pagination(
                [
                    'totalCount' => $packagistData['totalCount'],
                    'defaultPageSize' => $packagistData['currentPageCount'],
                    'forcePageParam' => false
                ]
            );
        }

        if ($packagistData['errorMessage']) {
            \Yii::$app->session->setFlash('error', $packagistData['errorMessage']);
        }

        return $this->render(
            'index',
            [
                'packages' => $packagistData['packages'],
                'totalCount' => $packagistData['totalCount'],
                'pagination' => $pagination,
                'sort' => $sort,
                'queryString' => $q
            ]
        );
    }

    /**
     * Displays package info
     *
     * @param string $vendorName
     * @param string $packageName
     * @param null|string $version
     *
     * @return string
     */
    public function actionPackage($vendorName, $packageName, $version = null)
    {
        $versions = [];
        $selectedVersion = null;
        $selectedVersionData = [];

        $keyCache = 'extension/package__package_' . md5(serialize([$vendorName, $packageName]));

        /** @var Package $package */
        $package = \Yii::$app->cache->get($keyCache);
        if ($package === false) {
            $package = (new PackagistApi())->getPackage($vendorName, $packageName);
            \Yii::$app->cache->set($keyCache, $package, Yii::$app->params['cache.extensions.get']);
        }

        if ($package) {
            $versions = array_values($package->getVersions());
            usort(
                $versions,
                function ($a, $b) {
                    return $a['version_normalized'] < $b['version_normalized'] ? 1 : -1;
                }
            );

            foreach ($versions as $versionItem) {
                if (
                    ($version !== null && $version !== $versionItem['version']) ||
                    ($version === null && mb_strpos($versionItem['version_normalized'], 'dev', null, 'UTF-8') !== false)
                ) {
                    continue;
                }

                $selectedVersion = $versionItem;
                break;
            }

            if ($selectedVersion === null && $versions) {
                $selectedVersion = $versions[0];
            }

            if ($selectedVersion) {
                foreach (['require', 'require-dev', 'suggest', 'provide', 'conflict', 'replace'] as $section) {
                    $selectedVersionData[$section] = [];

                    if (!empty($selectedVersion[$section])) {
                        foreach ($selectedVersion[$section] as $kVersionItem => $vVersionItem) {
                            if (preg_match('/^([a-z\d\-_]+)\/([a-z\d\-_]+)$/i', $kVersionItem, $m)) {
                                $str = Html::a(
                                    $kVersionItem,
                                    [
                                        'package',
                                        'vendorName' => $m[1],
                                        'packageName' => $m[2]
                                    ]
                                );
                            } else {
                                $str = Html::encode($kVersionItem);
                            }

                            $selectedVersionData[$section][] = ' - ' . $str . ' ' . Html::encode($vVersionItem);
                        }
                    }

                    if (!$selectedVersionData[$section]) {
                        $selectedVersionData[$section][] = '<small>[empty]</small>';
                    }
                }
            }
        } else {
            \Yii::$app->session->setFlash('error', 'Error get data from packagist.org');
        }

        return $this->render(
            'package',
            [
                'package' => $package,
                'readme' => (new PackagistApi())->getReadmeFromRepository($package->getRepository()),
                'versions' => $versions,
                'selectedVersion' => $selectedVersion,
                'selectedVersionData' => $selectedVersionData
            ]
        );
    }
}
