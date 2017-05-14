<?php
/**
 * Created by PhpStorm.
 * User: jakeroid
 * Date: 5/14/17
 * Time: 2:53 AM
 *
 * @link https://github.com/Jakeroid/yii2-hittable-behavior
 * @copyright Copyright (c) 2017 Jakeroid
 *
 * @link https://github.com/axgle/yii2-hitable-behavior
 * @copyright Copyright (c) 2016 Axgle
 *
 * @link https://github.com/usualdesigner/yii2-behaviors-hit
 * @copyright Copyright (c) 2015 Aleksey Bernackiy
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace jakeroid\yii2\behaviors;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\IntegrityException;
use yii\db\Query;

/**
 * HitCountableBehavior
 *
 * @property ActiveRecord $owner
 *
 * @author Aleksey Bernackiy <usualdesigner@gmail.com>
 * @author Ivan Karabadzhak <jakeroid.s@gmail.com>
 */
class HittableBehavior extends Behavior
{
    /**
     * @var string
     */
    public $attribute = 'hits_count';
    /**
     * @var bool
     */
    public $group = false;
    /**
     * @var int
     */
    public $delay = 86400;
    /**
     * @var string
     */
    public $table_name = '{{%hit}}';

    /**
     * @throws IntegrityException
     */
    public function init()
    {
        if (!$this->attribute) {
            throw new IntegrityException('Hits count attribute is not defined');
        }

        parent::init();
    }

    /**
     * @param \yii\base\Component $owner
     */
    public function attach($owner)
    {
        parent::attach($owner);

        if (!$this->group) {
            $this->group = get_class($this->owner);
        }
    }

    /**
     *
     */
    public function touch()
    {
        if ((date('U') - $this->getLatestVisit() > $this->delay) && !$this->getIsBot()) {
            if ($this->storeHit()) {
                $this->increaseCounter();
            }
        }
    }

    /**
     * @return mixed
     */
    public function getHitsCount()
    {
        return $this->owner->getAttribute($this->attribute);
    }

    /**
     * @return int
     * @throws \yii\db\Exception
     */
    private function storeHit()
    {
        return $this->owner->getDb()->createCommand()
            ->insert($this->table_name, [
                'user_agent' => Yii::$app->getRequest()->getUserAgent(),
                'ip' => Yii::$app->getRequest()->getUserIP(),
                'target_group' => $this->group,
                'target_pk' => $this->owner->primaryKey,
                'created_at' => date('U'),
            ])
            ->execute();
    }

    /**
     * @return bool
     */
    private function increaseCounter()
    {
        return $this->owner->updateCounters([
            $this->attribute => 1,
        ]);
    }

    /**
     * @return bool|string
     */
    private function getLatestVisit()
    {
        return (new Query())
            ->select('created_at')
            ->from($this->table_name)
            ->orderBy('created_at DESC')
            ->andWhere([
                'user_agent' => Yii::$app->getRequest()->getUserAgent(),
                'ip' => Yii::$app->getRequest()->getUserIP(),
                'target_group' => $this->group,
                'target_pk' => $this->owner->primaryKey,
            ])
            ->scalar();
    }

    /**
     * @return bool
     */
    protected function getIsBot()
    {
        return false;
    }
}