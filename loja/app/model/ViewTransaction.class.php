<?php
/**
 * Transaction Active Record
 * @author  <your-name-here>
 */
class ViewTransaction extends TRecord
{
    const TABLENAME = 'view_transaction';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
    create view view_transaction as
    SELECT et.*,
           ec.name as "customer_name",
           ec.email as "customer_email",
           ec.document as "customer_document",
           ep.description as "product_description"
      FROM eco_transaction et,
           eco_customer ec,
           eco_product ep
     WHERE et.customer_id=ec.id
       and et.product_id=ep.id
    
     **/
}

