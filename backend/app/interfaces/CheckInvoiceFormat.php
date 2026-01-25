<?php

namespace App\Interfaces;

interface CheckInvoiceFormat
{
    public function validateInvoiceFormat(string $invoiceNumber);
}
