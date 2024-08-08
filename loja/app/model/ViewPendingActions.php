<?php
/**
 * PaymentStatus Active Record
 * @author  <your-name-here>
 */
class ViewPendingActions extends TRecord
{
    const TABLENAME = 'view_pending_actions';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
}

/*
CREATE VIEW view_pending_actions
AS
  SELECT eco_transaction.id      AS transaction_id,
         eco_product.description AS product_description,
         eco_transaction.shipping_code,
         eco_transaction.external_id,
         eco_transaction.operation_date,
         eco_action.id           AS action_id,
         eco_action.remote_method,
         eco_customer.name       AS customer_name,
         eco_customer.email      AS customer_email,
         eco_product.id          AS product_id,
         eco_product.confirmation_mail
  FROM   eco_transaction,
         eco_customer,
         eco_product,
         eco_product_action,
         eco_action
  WHERE  ( ( eco_transaction.product_id = eco_product.id )
           AND ( eco_transaction.customer_id = eco_customer.id )
           AND ( eco_product_action.product_id = eco_product.id )
           AND ( eco_product_action.action_id = eco_action.id )
           AND ( eco_transaction.paymentstatus_id IN ( 3, 4 ) )
           AND ( NOT ( EXISTS (SELECT 1
                               FROM   eco_transaction sub_tr,
                                      eco_transaction_action sub_tr_act,
                                      eco_action sub_act
                               WHERE  ( ( sub_tr_act.transaction_id = sub_tr.id )
                                    AND ( sub_tr_act.action_id = sub_act.id )
                                    AND ( sub_tr.id  = eco_transaction.id )      // aqui conecta inner com outter (transaction)
                                    AND ( sub_act.id = eco_action.id ) )) ) ) )  // aqui conecta inner com outter (action)
  ORDER  BY eco_transaction.id,
            eco_action.id; 
  */