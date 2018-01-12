<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Eccube\Tests\Repository;

use Eccube\Tests\EccubeTestCase;

class PaymentRepositoryTest extends EccubeTestCase
{

    public function setUp()
    {
        $this->markTestIncomplete(get_class($this).' は未実装です');
        parent::setUp();
    }

    public function test_findAllowedPayment()
    {
        $saleTypes = array(7, 6);
        $saleTypes = array_unique($saleTypes);

        // $paymentOption = $app['eccube.repository.payment_option']->getPaymentOption($saleTypes);
        $deliveries = $this->app['eccube.repository.delivery']->getDeliveries($saleTypes);

        // 支払方法を取得
        $payments = $this->app['eccube.repository.payment']->findAllowedPayments($deliveries);

        $this->expected = 0;
        $this->actual = count($payments);
        $this->verify('存在しない販売種別を指定しているため取得できない');

        if (count($saleTypes) > 1) {
            $deliveries = $this->app['eccube.repository.delivery']->findAllowedDeliveries($saleTypes, $payments);
        }
    }

    public function testFindAllowedPaymentWithDefault()
    {
        $saleTypes = array(1);
        $saleTypes = array_unique($saleTypes);

        // $paymentOption = $app['eccube.repository.payment_option']->getPaymentOption($saleTypes);
        $deliveries = $this->app['eccube.repository.delivery']->getDeliveries($saleTypes);

        // 支払方法を取得
        $payments = $this->app['eccube.repository.payment']->findAllowedPayments($deliveries);

        $this->expected = 4;
        $this->actual = count($payments);
        $this->verify();
    }

    public function testFindAllowedPaymentWithDeliveryOnly()
    {
        $saleTypes = array(1, 2);
        $saleTypes = array_unique($saleTypes);

        // $paymentOption = $app['eccube.repository.payment_option']->getPaymentOption($saleTypes);
        $deliveries = $this->app['eccube.repository.delivery']->getDeliveries($saleTypes);

        // 支払方法を取得
        $payments = $this->app['eccube.repository.payment']->findAllowedPayments($deliveries);

        $this->expected = 1;
        $this->actual = count($payments);
        $this->verify('販売種別共通の支払い方法は'.$this->expected.'種類です');
    }

    /**
     * 共通する支払い方法が存在しない場合.
     *
     * @link https://github.com/EC-CUBE/ec-cube/issues/1162
     */
    public function testFindAllowedPaymentWithExclusion()
    {
        $saleTypes = array(1, 2);
        $saleTypes = array_unique($saleTypes);

        // SaleType 1 と 2 で, 共通する支払い方法を削除しておく
        $PaymentOption = $this
            ->app['orm.em']
            ->getRepository('\Eccube\Entity\PaymentOption')
            ->findOneBy(
                array(
                    'delivery_id' => 1,
                    'payment_id' => 3
                )
            );
        $this->assertNotNull($PaymentOption);
        $this->app['orm.em']->remove($PaymentOption);
        $this->app['orm.em']->flush();

        $deliveries = $this->app['eccube.repository.delivery']->getDeliveries($saleTypes);

        // 支払方法を取得
        $payments = $this->app['eccube.repository.payment']->findAllowedPayments($deliveries);

        $this->expected = 0;
        $this->actual = count($payments);
        $this->verify('販売種別共通の支払い方法は'.$this->expected.'種類です');
    }

    public function testFindAllArray()
    {
        $Results = $this->app['eccube.repository.payment']->findAllArray();

        $this->assertTrue(is_array($Results));

        $this->expected = '銀行振込';
        $this->actual = $Results[3]['method'];
        $this->verify();
    }

    public function testFindPayments()
    {
        $saleTypes = array(1);
        $saleTypes = array_unique($saleTypes);

        // $paymentOption = $app['eccube.repository.payment_option']->getPaymentOption($saleTypes);
        $deliveries = $this->app['eccube.repository.delivery']->getDeliveries($saleTypes);

        // 支払方法を取得
        $payments = $this->app['eccube.repository.payment']->findPayments($deliveries[0]);

        $this->expected = 4;
        $this->actual = count($payments);
        $this->verify();

        $this->assertTrue(is_array($payments[0]));
    }

    public function testFindPaymentsAsObjects()
    {
        $saleTypes = array(1);
        $saleTypes = array_unique($saleTypes);

        // $paymentOption = $app['eccube.repository.payment_option']->getPaymentOption($saleTypes);
        $deliveries = $this->app['eccube.repository.delivery']->getDeliveries($saleTypes);

        // 支払方法を取得
        $payments = $this->app['eccube.repository.payment']->findPayments($deliveries[0], true);

        $this->expected = 4;
        $this->actual = count($payments);
        $this->verify();

        $this->assertTrue(is_object($payments[0]));
    }
}
