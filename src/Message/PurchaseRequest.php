<?php
/**
 * RoboKassa driver for Omnipay PHP payment library.
 *
 * @link      https://github.com/hiqdev/omnipay-robokassa
 * @package   omnipay-robokassa
 * @license   MIT
 * @copyright Copyright (c) 2015-2017, HiQDev (http://hiqdev.com/)
 */

namespace Omnipay\RoboKassa\Message;

class PurchaseRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate(
            'purse', 'amount', 'currency', 'description'
        );

        return [
            'InvId' => '0',
            'MrchLogin' => $this->getPurse(),
            'OutSum' => $this->getAmount(),
            'Desc' => $this->getDescription(),
            'IncCurrLabel' => $this->getCurrency(),
            'SignatureValue' => $this->generateSignature(),
            'IsTest' => (int)$this->getTestMode(),
        ] + $this->getCustomFields();
    }

    public function generateSignature()
    {
        $params = [
            $this->getPurse(),
            $this->getAmount(),
            '0',
            $this->getSecretKey()
        ];

        foreach ($this->getCustomFields() as $field => $value) {
            $params[] = "$field=$value";
        }

        return md5(implode(':', $params));
    }

    public function getCustomFields()
    {
        $fields = array_filter([
            'Shp_TransactionId' => $this->getTransactionId(),
            'Shp_Client' => $this->getClient(),
            'Shp_Currency' => $this->getCurrency()
        ]);

        ksort($fields);

        return $fields;
    }

    public function sendData($data)
    {
        return $this->response = new PurchaseResponse($this, $data);
    }
}
