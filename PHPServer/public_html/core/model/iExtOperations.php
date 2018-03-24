<?php
/*
  iExtView interface used to edit table objects from the admin screen
  This interface should return array objects
 */
interface iExtOperations{

    //array of form items
    function GetExtForm();

    //array of columns
    function GetExtColumns();

    //array defining the model
    function GetExtModel();

    //array defining the store
    function GetExtStore();

    //array to put together all of the arrays from the above functions
    function GetExtData();
}

