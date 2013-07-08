# QRPlatba PHP - SPAYD

Simple class for generating *QR Platba* http://qr-platba.cz/

Only Czech accounts supported!

## Example

       $platba = new QRPlatba('275154463/300', 500.50);
       $platba->setMessage("Za rohliky a housky");
       $platba->setVariableSym("123456789");
       echo $platba;
