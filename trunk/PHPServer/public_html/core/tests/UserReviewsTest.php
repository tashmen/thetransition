<?php

/**
 * Test for userreviews class
 *
 */
class UserReviewsTest extends BaseTest {
    
    public function TestInternal()
    {
        $this->TestUserReviews();
        $this->TestGetExtData();
    }
    
    private function TestGetExtData()
    {
        $response = $this->PostToSelf('userreviews', 'extdata', $this->GetUser(1), null);
        $this->Assert('true', $response->success, 'extdata was not successful');
        $form = $response->userreviews->form;
        $this->Assert('fieldset', $form[0]->xtype);
        $items = $form[0]->items;
        $this->Assert($items[0]->xtype, 'numberfield');
        $this->Assert($items[0]->name, 'reviewerid');
        $columns = $response->userreviews->columns;
        $this->Assert($columns[0]->dataIndex, 'reviewerid');
        $model = $response->userreviews->model;
        $this->Assert($model[0]->name, 'reviewerid');
        $store = $response->userreviews->store;
        
        var_dump($store);
    }
    
    private function TestUserReviews() {
        $data = '[{\"reviewerid\":\"1\",\"revieweeid\":\"2\",\"id\":\"UserReviews-1\",\"name\":\"testname\",\"review\":\"testreview\"}]';
        $user =  $this->GetUser(1);     
        $resource = 'userreviews';
         
        $response = $this->PostToSelf($resource, 'create', $user, $data);
        $this->Assert('true', $response->success, 'create was not successful');
        $userReview = $response->userreviews[0];
        $this->Assert('1', $userReview->reviewerid, 'Reviewer id does not match');
        $this->Assert('2', $userReview->revieweeid, 'Reviewee id does not match');
        $this->Assert('testname', $userReview->name, 'name does not match');
        $this->Assert('testreview', $userReview->review, 'review does not match');
        
        //TODO: Add Update and then Read; read should check that update occurred
        
        $this->PostToSelf($resource, 'delete', $user, $data);
        $this->Assert('true', $response->success, 'delete was not successful');
    }
}
