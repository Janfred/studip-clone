<?
/*
* This class implements an alternative mechanism to copy an Stud.IP Seminar
* DataCopyTool.class.php

* written by Dirk Oelkers <d.oelkers@fh-wolfenbuettel.de>

* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.

* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.

* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

require_once("lib/phplib_local.inc.php");
require_once("DataTree.class.php");
require_once("Item.class.php");

class DataCopyTool {

	private $source_seminar_id;
	private $target_seminar_id;
	private $source_top_folder_id;
	private $target_top_folder_id;
	private $user_id;
	private $db;


	private $itemTree;

	private $seminar_sql;
	private $seminar_inst_sql;
	private $seminar_sem_tree_sql;
	
	private $discussionOldNewMapping;

	function __construct ( $source_seminar_id, $target_seminar_id=null, $user_id )
	{
		$this->source_seminar_id = $source_seminar_id;
		$this->target_seminar_id = $target_seminar_id;

		if ( (! $source_seminar_id) || (! $user_id) )
		{
			$error = 'Need at least Parameters: $source_seminar_id and $user_id to instance this class DataCopyTool';
			throw new Exception($error);
		}

		$this->source_top_folder_id = md5( $source_seminar_id."top_folder" );
		$this->target_top_folder_id = md5( $target_seminar_id."top_folder" );

		$this->user_id = $user_id;

		$this->seminar_sql ="select * from seminare where seminar_id = '";
		$this->seminar_inst_sql = "select * from seminar_inst where seminar_id = '";
		$this->seminar_sem_tree_sql = "select * from seminar_sem_tree where seminar_id = '";
		$this->seminar_user_sql="select * from seminar_user where status = 'dozent' and Seminar_id = '";

	}

	// seminar metadata (table seminare) , seminar/institute relation (table seminar_inst) , seminar_sem_tree
	function copySeminar( $newSemData )
	{
		$db = new DB_Seminar();
		$db1 = new DB_Seminar();

		$timestamp = time();

		if ( is_null ( $this->target_seminar_id ) )
		{
			$id = $this->createNewId();
			$this->setTargetSeminarId ( $id );
		}

		$sql = $this->seminar_sql.$this->source_seminar_id."'";
		$results = $db->query( $sql );
		//echo $sql."<br><br>";

		$db->next_record();
		$record = $db->Record;

		$metadata = $db->metadata( "seminare",true );
		$numOfFields = $metadata["num_fields"];

		$insertSql = "insert into seminare set ";
		$comma = ",";

		for ($i=0;$i<$numOfFields;$i++)
		{
			$fieldname = $metadata[$i]["name"];
			switch ($fieldname)
			{
				case "Seminar_id":
					$value = $this->target_seminar_id;
					break;
				case "Name":
					$value = $newSemData["Name"];
					break;
				case "mkdate":
					$value = $timestamp;
					break;
				case "chdate":
					$value = $timestamp;
					break;
				default:
					$value = $record[$metadata[$i]["name"]];
			}

			if ( $i ==  $numOfFields - 1) //reaching the end of the query string (last loop)
			{
				$comma = "";
			}

			if ( $metadata[$i]["type"] != "int" )
			{
				$insertSql = $insertSql." ".$fieldname."='".$value."'".$comma;
			}
			else
			{
				$insertSql = $insertSql." ".$fieldname."=".$value.$comma;
			}

		}

		$db1->query( $insertSql );
		//echo $insertSql."<br><br>";

		//---copy seminar institute relation

		$sql = $this->seminar_inst_sql.$this->source_seminar_id."'";
		$results = $db->query( $sql );
		//echo $sql."<br><br>";

		$db->next_record();
		$record = $db->Record;
		$numOfFields = $metadata["num_fields"];

		$insertSql = "insert into seminar_inst values ('".$this->target_seminar_id."',"."'".$record["institut_id"]."')";

		$db1->query( $insertSql );
		//echo $insertSql."<br><br>";
		//---copy seminar semtree relation

		$sql = $this->seminar_sem_tree_sql.$this->source_seminar_id."'";
		$results = $db->query( $sql );
		//echo $sql."<br><br>";

		$db->next_record();
		$record = $db->Record;
		$numOfFields = $metadata["num_fields"];

		$insertSql = "insert into seminar_sem_tree values ('".$this->target_seminar_id."',"."'".$record["sem_tree_id"]."')";

		$db1->query( $insertSql );
		//echo $insertSql."<br><br>";

		//copy dozent user

		$sql = $this->seminar_user_sql.$this->source_seminar_id."'";
		$results = $db->query( $sql );
		//echo $sql."<br><br>";

		$db->next_record();
		$record = $db->Record;

		$metadata = $db->metadata( "seminar_user",true );
		$numOfFields = $metadata["num_fields"];

		$insertSql = "insert into seminar_user set ";
		$comma = ",";

		for ($i=0;$i<$numOfFields;$i++)
		{
			$fieldname = $metadata[$i]["name"];
			switch ($fieldname)
			{
				case "Seminar_id":
					$value = $this->target_seminar_id;
					break;
				case "mkdate":
					$value = $timestamp;
					break;
				case "chdate":
					$value = $timestamp;
					break;
				default:
					$value = $record[$metadata[$i]["name"]];
			}

			if ( $i ==  $numOfFields - 1) //reaching the end of the query string (last loop)
			{
				$comma = "";
			}

			if ( $metadata[$i]["type"] != "int" )
			{
				$insertSql = $insertSql." ".$fieldname."='".$value."'".$comma;
			}
			else
			{
				$insertSql = $insertSql." ".$fieldname."=".$value.$comma;
			}

		}

		$db1->query( $insertSql );
		//echo $insertSql."<br><br>";

		return $this->target_seminar_id;
	}

	// check if a given old topic_id exists in the mapping table 
	// returns the new topic_id if successful
	// otherwise returns false
	private function checkDiscussionMapping( $old_topic_id )
	{
		if (is_null( $this->discussionOldNewMapping ))
		{
			return 0;
		}
		
		if ( array_key_exists( $old_topic_id , $this->discussionOldNewMapping ) )
		{
			return $this->discussionOldNewMapping[ $old_topic_id ];
		}
		else 
		{
			return 0;
		}
	}
	
	//copy the Wiki Content page from the old seminar to the new seminar
	function copyWiki()
	{
		$dbw = new DB_Seminar();
		$dbw1 = new DB_Seminar();

		//copy the internal wiki content from the old seminar to the new seminar
		$metadata = $dbw->metadata( "wiki",true );

		$sourceSeminarID = $this->source_seminar_id;
		$targetSeminarID = $this->target_seminar_id;

		$results = $dbw->query( "select * from wiki where range_id ='".$sourceSeminarID."'" );

		if ( ! $results )
		{
			return false;
		}

		$count = $metadata["num_fields"] -1;

		while ( $dbw->next_record() )
		{
			$record = $dbw->Record;

			$insertSql = "INSERT INTO wiki SET range_id='".$targetSeminarID."', ";
			$i=1;
			for ($i=1;$i<$count;$i++)
			{
				if ( $metadata[$i]["type"] != "int" )
				{
					$insertSql = $insertSql." ".$metadata[$i]["name"]."='".$record[$metadata[$i]["name"]]."',";
				}
				else
				{
					$insertSql = $insertSql." ".$metadata[$i]["name"]."=".$record[$metadata[$i]["name"]].",";
				}
			}
			// the last field
			if ( $metadata[$i]["type"] == "string" || $metadata[$i]["type"] == "bin" )
			{
				$insertSql = $insertSql." ".$metadata[$i]["name"]."='".$record[$metadata[$i]["name"]];
			}
			else
			{
				$insertSql = $insertSql." ".$metadata[$i]["name"]."=".$record[$metadata[$i]["name"]];
			}

			$results = $dbw1->query( $insertSql );

			if ( ! $results )
			{
				return false;
			}

		}

		return true;
	}

	//copy the Info page from the old seminar to the new seminar
	function copyScm()
	{

		$dbw = new DB_Seminar();
		$dbw1 = new DB_Seminar();

		//copy the internal wiki content from the old seminar to the new seminar
		$metadata = $dbw->metadata( "scm",true );

		$sourceSeminarID = $this->source_seminar_id;
		$targetSeminarID = $this->target_seminar_id;

		$results = $dbw->query( "select * from scm where range_id ='".$sourceSeminarID."'" );

		if ( ! $results )
		{
			return false;
		}

		$count = $metadata["num_fields"] -1;

		while ( $dbw->next_record() )
		{
			$record = $dbw->Record;
			$new_scm_id = uniqid( md5( rand() ), true );
			$insertSql = "INSERT INTO scm SET scm_id = '".$new_scm_id."', range_id='".$targetSeminarID."', ";
			$i=0;
			for ($i=2;$i<$count;$i++)
			{
				if ($metadata[$i]["name"] == "range_id" )
				continue;

				if ( $metadata[$i]["type"] != "int" )
				{
					$insertSql = $insertSql." ".$metadata[$i]["name"]."='".$record[$metadata[$i]["name"]]."',";
				}
				else
				{
					$insertSql = $insertSql." ".$metadata[$i]["name"]."=".$record[$metadata[$i]["name"]].",";
				}
			}
			// the last field
			if ( $metadata[$i]["type"] == "string" || $metadata[$i]["type"] == "bin" )
			{
				$insertSql = $insertSql." ".$metadata[$i]["name"]."='".$record[$metadata[$i]["name"]];
			}
			else
			{
				$insertSql = $insertSql." ".$metadata[$i]["name"]."=".$record[$metadata[$i]["name"]];
			}

			$results = $dbw1->query( $insertSql );

			if ( ! $results )
			{
				return false;
			}

		}

		return true;
	}


	function copyContentData( $wantFlags=null )
	{
		if ( is_null( $this->itemTree ) )
		{
			$this->itemTree = new DataTree($this->source_seminar_id,$this->user_id,$wantFlags);

			$this->itemTree->buildTree();
		}
		
		$item = $this->itemTree->getRootItem();

		$this->db = new DB_Seminar();
		
		//copy common discussion entrys
		$this->copyDiscussion();
		
		// start the copy process by calling the recursive function copySubTree
		// $item is of type Item::ROOT what means that its range_id is the seminar_id of the source seminar
		// therefore the target range_id has to be the seminar_id of the target seminar
		$this->copySubTree( $item, $this->target_seminar_id );

	}

	// copy the items contained in the given item
	//recursive
	private function copySubTree( $item, $target_range_id )
	{

		if ( is_null($item) )
		{
			return false;
		}
		
		$children = $item->getChildren();

		$numOfChildren = $item->getNumOfChildren();

		for($i=0;$i<$numOfChildren;$i++)
		{
			$child = $children[$i];

			if ( $child->getItemType() == Item::DOWNLOAD_FOLDER )
			{
				$old_range_id = $child->getFieldValue("range_id");
				if( $old_range_id == $this->source_top_folder_id )
				{
					$new_folder_id = $this->copyFolderItem( $child,  $this->target_top_folder_id );
				}
				else
				{
					$new_folder_id = $this->copyFolderItem( $child,  $target_range_id);
				}
				$this->copySubTree( $child, $new_folder_id );
				
				continue;
			}

			if ( $child->getItemType() == Item::GROUP_DATA )
			{
				$statusgruppe_id = $this->copyStatusgruppe($child,  $target_range_id);

				$statusgruppeChildren = $child->getChildrenPerItemType( Item::GROUP_FOLDER  );

				if ( ! is_null( $statusgruppeChildren ) )
				{

					$numOfStatusgruppeChildren = $statusgruppeChildren["num_children"];

					for($i1=0;$i1<$numOfStatusgruppeChildren;$i1++)  // actually there should be only one, but who knows the future
					{
						$groupFolderItem = $statusgruppeChildren[$i1];
						$new_folder_id = $this->copyFolderItem( $groupFolderItem, $statusgruppe_id );
						$this->copySubTree( $groupFolderItem, $new_folder_id );
					}

				}
				//$new_folder_id = $this->copyGroupFolderItem( $child,  $target_range_id);

				continue;
			}

			// copy schedules (table: termine)
			if ( $child->getItemType() == Item::SCHEDULE )
			{
				$new_termin_id = $this->copyScheduleItem( $child, $target_range_id );
				//$scheduleChildren = $child->getChildren();

				$scheduleChildren = $child->getChildrenPerItemType( Item::ISSUE_DATA );

				// related issue data (table: themen, relation between table termine and table themen by crosstable themen_termine )
				if ( ! is_null( $scheduleChildren ) )
				{
					$numOfScheduleChildren = $scheduleChildren["num_children"];

					for($i1=0;$i1<$numOfScheduleChildren;$i1++)  // actually there should be only one, but who knows the future
					{

						$issueItem = $scheduleChildren[ $i1 ];

						$new_issue_id = $this->copyIssueItem( $issueItem, $target_range_id );

						$id0 = "'" . $new_issue_id . "'";

						$id1 = "'" . $new_termin_id . "'";

						$this->updateCrosstable( "themen_termine",$id0 ,$id1 );

						$issueChildren = $issueItem->getChildrenPerItemType( Item::ISSUE_FOLDER );

						// related issue folder (table: folder folder.range_id = themen.issue_id )
						if( ! is_null( $issueChildren ) )
						{
							$numOfIssueChildren = $issueChildren["num_children"];

							for($i2=0;$i2<$numOfIssueChildren;$i2++)  // actually there should be only one, but who knows the future
							{
								$issueFolderItem = $issueChildren[$i2];
								$this->copyFolderItem( $issueFolderItem, $new_issue_id );
							}
						}

						$issueChildren = $issueItem->getChildrenPerItemType( Item::ISSUE_DISCUSSION );
						
						// related issue discussion (table: px_topics px_topics.topic_id = themen.issue_id )
						if( ! is_null( $issueChildren ) )
						{
							$numOfIssueChildren = $issueChildren["num_children"];

							for($i2=0;$i2<$numOfIssueChildren;$i2++) 
							{

								//$item->getFieldValue("parent_id");
								$issueDiscussionItem = $issueChildren[$i2];
								$discussionParent_id = $issueDiscussionItem->getFieldValue("parent_id");
								
								$newDiscussionParent_id = '0';
								if ( $discussionParent_id != '0' )
								{
									$newDiscussionParent_id = $this->checkDiscussionMapping( $discussionParent_id );
								}
								
								$this->copyDiscussionSubtree($issueDiscussionItem,$newDiscussionParent_id,$new_issue_id); 
							}
						}
					}
					
				}

			}
			if ( $child->getItemType() == Item::DOCUMENT || $child->getItemType() == Item::HYPERLINK )
			{
			$this->copyDocumentItem( $child, $target_range_id );
			}
		}

		return true;
	}

	//copy all common discussion entrys
	private function copyDiscussion( )
	{
		$item =  $this->itemTree->getRootItem();

		$children = $item->getChildrenPerItemType( Item::DISCUSSION );
		
		$numOfChildren = $children["num_children"];
		
		for ($i=0;$i<$numOfChildren;$i++)
		{
			$child = $children[$i];
			$this->copyDiscussionSubtree( $child );
		}

	}

	/*
	* copies an discussion subtree (recursive)
	* $item: item of type ITEM::DISCUSSION
	* $newParent_id: new id of the prior copied parent item
	*/
	private function copyDiscussionSubtree( Item $item, $newParent_id='0',$newIssue_id='0' )
	{
		
		$newDiscussion_id=$this->copyDiscussionItem( $item,$newParent_id,$newIssue_id );

		$oldDiscussion_id=$item->getFieldValue( "topic_id" );
		
		$this->discussionOldNewMapping[$oldDiscussion_id] = $newDiscussion_id;
		
		if ( $item->hasChildren() )
		{
			$children = $item->getChildren();
			$numOfDiscussionChildren = $item->getNumOfChildren();

			for($i1=0;$i1<$numOfDiscussionChildren;$i1++)
			{
				$child = $children[$i1];
				$this->copyDiscussionSubtree( $child,$newDiscussion_id.'0' );
			}
		}

	}

	/*
	universal function to update crosstables with two columns
	$tableName: name of the crosstable
	$id0: id value for the first column
	$id1: id value for the second column
	*/
	private function updateCrosstable( $tableName, $id0,$id1 )
	{
		$sql = "INSERT INTO ".$tableName." VALUES ( ".$id0.",".$id1." )";

		$result = $this->db->query( $sql );

		return $result;
	}

	/*
	make an copy of the given item into table themen
	var $item must be of type Item:ISSUE_DATA
	$target_range_id actually is the new seminar_id
	*/
	private function copyIssueItem( Item $item, $target_range_id )
	{

		$timestamp = time();

		$new_issue_id=0;
		$sql = "INSERT INTO themen SET ";

		//$data = $item->getData();
		$fieldNameList = $item->getFieldNameList();
		$numOfFields = $item->getNumOfFields();

		$comma = ",";

		for ($i=0;$i<$numOfFields;$i++)
		{
			$fieldName = $fieldNameList[$i];
			$fieldType = $item->getFieldType( $fieldName );

			switch ( $fieldName )
			{
				case "issue_id":

					$value = $this->createNewId();
					$new_issue_id = $value;
					break;

				case "seminar_id":

					$value = $target_range_id;
					break;

				case "author_id":
					$value = $this->user_id;
					break;

				case "chdate":

					$value = $timestamp;
					break;

					/* hold the old dates to hold the original order in view 
				case "mkdate":

					$value = $timestamp;
					break; */

				default:
					$value = $item->getFieldValue( $fieldName );
			}

			if ( $i ==  $numOfFields - 1) //reaching the end of the query string (last loop)
			{
				$comma = "";
			}

			if ( strcmp( $fieldType, "int") != 0  )
			{
				$sql = $sql." ".$fieldName." = '".$value."'".$comma;
			}
			else
			{
				if ( is_null( $value ) )
				{
					$value = 0;
				}
				$sql = $sql." ".$fieldName." = ".$value.$comma;
			}
		}

		$result = $this->db->query( $sql );

		if ( ! $result )
		{
			return false;
		}

		return $new_issue_id;

	}

	//make an copy of the given item into table termine
	//var $item must be of type Item:SCHEDULE
	private function copyScheduleItem( Item $item )
	{
		$timestamp = time();

		$new_termin_id=0;
		$sql = "INSERT INTO termine SET ";

		//$data = $item->getData();
		$fieldNameList = $item->getFieldNameList();
		$numOfFields = $item->getNumOfFields();

		$comma = ",";

		for ($i=0;$i<$numOfFields;$i++)
		{
			$fieldName = $fieldNameList[$i];
			$fieldType = $item->getFieldType( $fieldName );

			switch ( $fieldName )
			{
				case "termin_id":

					$value = $this->createNewId();
					$new_termin_id = $value;
					break;

				case "range_id":

					$value = $this->target_seminar_id;
					break;

				case "author_id":
					$value = $this->user_id;
					break;

				case "chdate":

					$value = $timestamp;
					break;

				case "mkdate":

					$value = $timestamp;
					break;

				default:
					$value = $item->getFieldValue( $fieldName );
			}

			if ( $i ==  $numOfFields - 1) //reaching the end of the query string (last loop)
			{
				$comma = "";
			}

			if ( strcmp( $fieldType, "int") != 0  )
			{
				$sql = $sql." ".$fieldName." = '".$value."'".$comma;
			}
			else
			{
				$sql = $sql." ".$fieldName." = ".$value.$comma;
			}
		}

		$result = $this->db->query( $sql );

		if ( ! $result )
		{
			return false;
		}

		return $new_termin_id;
	}


	// $item : Object of type Item containing the data of an document
	// ( not the content, just the meta data ) filename on filesystem == dokument_id
	// $target_range_id : String containing the new range_id
	// range_id can be a seminar_id, md5( $seminar_id."top_folder"), another folder_id or a statusgruppe_id from table statusgruppen
	// so range_id could be described as an id for an location in Stud.IP
	private function copyDocumentItem( Item $item , $target_range_id )
	{
		global $UPLOAD_PATH;

		$timestamp = time();

		$old_document_id = $item->getFieldValue( "dokument_id" );
		$new_document_id = $this->createNewId();

		if ( $item->getItemType() == Item::DOCUMENT )
		{
			if (!copy($UPLOAD_PATH . '/' . $old_document_id, $UPLOAD_PATH . '/' . $new_document_id)){
				return false;
			}
		}
		$sql = "INSERT INTO dokumente SET ";

		//$data = $item->getData();
		$fieldNameList = $item->getFieldNameList();
		$numOfFields = $item->getNumOfFields();

		$comma = ",";

		for ($i=0;$i<$numOfFields;$i++)
		{
			$fieldName = $fieldNameList[$i];
			$fieldType = $item->getFieldType( $fieldName );

			$value = $item->getFieldValue( $fieldName );

			switch ( $fieldName )
			{
				case "dokument_id":

					$value = $new_document_id;
					break;

				case "range_id":

					$value = $target_range_id;
					break;

				case "seminar_id":

					$value = $this->target_seminar_id;
					break;

				case "user_id":
					$value = $this->user_id;
					break;

				case "chdate":

					$value = $timestamp;
					break;

				case "mkdate":

					$value = $timestamp;
					break;

				case "downloads":

					$value=0;
					break;
			}


			if ( $i ==  $numOfFields - 1) //reaching the end of the query string (last loop)
			{
				$comma = "";
			}

			if ( strcmp( $fieldType, "int") != 0  )
			{
				$sql = $sql." ".$fieldName." = '".$value."'".$comma;
			}
			else
			{
				$sql = $sql." ".$fieldName." = ".$value.$comma;
			}
		}

		$result = $this->db->query( $sql );

		if ( ! $result )
		{
			return false;
		}

		return $new_document_id;
	}

	// $item : Object of type Item containing the data of an download folder
	// or an status group folder  ( folder for an Seminar user group )
	// $target_range_id : String containing the new range_id
	// range_id can be a seminar_id, md5( $seminar_id."top_folder"), another folder_id or a statusgruppe_id from table statusgruppen
	// so range_id could be described as an id for an location in Stud.IP
	private function copyFolderItem( $item , $target_range_id )
	{
		$timestamp = time();

		$new_folder_id=0;
		$sql = "INSERT INTO folder SET ";

		//$data = $item->getData();
		$fieldNameList = $item->getFieldNameList();
		$numOfFields = $item->getNumOfFields();

		$comma = ",";

		for ($i=0;$i<$numOfFields;$i++)
		{
			$fieldName = $fieldNameList[$i];
			$fieldType = $item->getFieldType( $fieldName );

			switch ( $fieldName )
			{
				case "folder_id":

					$value = $this->createNewId();
					$new_folder_id = $value;
					break;

				case "range_id":

					$value = $target_range_id;
					break;

				case "user_id":
					$value = $this->user_id;
					break;

				case "chdate":

					$value = $timestamp;
					break;

				case "mkdate":

					$value = $timestamp;
					break;

				default:
					$value = $item->getFieldValue( $fieldName );
			}

			if ( $i ==  $numOfFields - 1) //reaching the end of the query string (last loop)
			{
				$comma = "";
			}

			if ( strcmp( $fieldType, "int") != 0  )
			{
				$sql = $sql." ".$fieldName." = '".$value."'".$comma;
			}
			else
			{
				$sql = $sql." ".$fieldName." = ".$value.$comma;
			}
		}

		$result = $this->db->query( $sql );

		if ( ! $result )
		{
			return false;
		}

		return $new_folder_id;
	}


	//make an copy of the given item into table statusgruppen
	//var $item mus be of type Item:GROUP_DATA
	//$target_range_id is in fact the new seminar_id
	private function copyStatusgruppe($item, $target_range_id)
	{
		$timestamp = time();
		/*
		$children = $item->getChildrenPerItemType( Item::GROUP_DATA );
		if ( ! $children )
		{
		echo "function: copyStatusgruppe / Got no Item::GROUP_DATA results";
		return false;
		}
		if ($children["num_children"] > 1) // I expect only one at this point
		{
		echo "function: copyStatusgruppe / Too many results of type Item::GROUP_DATA";
		}

		$groupDataItem = $children[0];
		*/
		$groupDataItem = $item;
		// lets go !

		$new_statusgruppe_id=0;
		$sql = "INSERT INTO statusgruppen SET ";

		//$data = $item->getData();
		$fieldNameList = $groupDataItem->getFieldNameList();
		$numOfFields = $groupDataItem->getNumOfFields();

		$comma = ",";

		for ($i=0;$i<$numOfFields;$i++)
		{
			$fieldName = $fieldNameList[$i];
			$fieldType = $groupDataItem->getFieldType( $fieldName );

			switch ( $fieldName )
			{
				case "statusgruppe_id":

					$value = $this->createNewId();
					$new_statusgruppe_id = $value;
					break;

				case "range_id":

					$value = $target_range_id;
					break;

				case "mkdate":

					$value = $timestamp;
					break;

				case "chdate":

					$value = $timestamp;
					break;

				default:
					$value = $groupDataItem->getFieldValue( $fieldName );
			}

			if ( $i ==  $numOfFields - 1) //reaching the end of the query string (last loop)
			{
				$comma = "";
			}

			if ( strcmp( $fieldType, "int") != 0  )
			{
				$sql = $sql." ".$fieldName." = '".$value."'".$comma;
			}
			else
			{
				$sql = $sql." ".$fieldName." = ".$value.$comma;
			}
		}

		$result = $this->db->query( $sql );

		if ( ! $result )
		{
			return false;
		}

		return $new_statusgruppe_id;
	}

	/*
	make an copy of the given item into table px_topics
	var $item must be of type Item:DISCUSSION
	$parent_id is the id of the issue in the discussion tree ( that does not mean the issue from table themen )
	looks like a folder in discussion tree view
	*/
	private function copyDiscussionItem( Item $item, $parent_id='0',$newIssue_id='0' )
	{

		$timestamp = time();

		$new_issue_id=0;
		$sql = "INSERT INTO px_topics SET ";

		//$data = $item->getData();
		$fieldNameList = $item->getFieldNameList();
		$numOfFields = $item->getNumOfFields();

		$comma = ",";

		if ( $newIssue_id == '0' )
		{
			$new_topic_id = $this->createNewId();
		}
		else 
		{
			$new_topic_id = $newIssue_id;
		}
		
		for ($i=0;$i<$numOfFields;$i++)
		{
			$fieldName = $fieldNameList[$i];
			$fieldType = $item->getFieldType( $fieldName );

			switch ( $fieldName )
			{
				case "topic_id":

					$value = $new_topic_id;
					break;

				case "root_id":
					if ( $parent_id=='0' ) // if theme entry root_id == topic_id
					{
						$value = $new_topic_id;
					}
					else
					{
						$value = $parent_id;
					}
					break;

				case "parent_id":
					$value = $parent_id;
					break;

				case "Seminar_id":

					$value = $this->target_seminar_id;
					break;

				case "parent_id":
					$value = $parent_id;

				case "chdate":

					$value = $timestamp;
					break;

				case "mkdate":

					$value = $timestamp;
					break;

				default:
					$value = $item->getFieldValue( $fieldName );
			}

			if ( $i ==  $numOfFields - 1) //reaching the end of the query string (last loop)
			{
				$comma = "";
			}

			if ( strcmp( $fieldType, "int") != 0  )
			{
				$sql = $sql." ".$fieldName." = '".$value."'".$comma;
			}
			else
			{
				if ( is_null( $value ) )
				{
					$value = 0;
				}
				$sql = $sql." ".$fieldName." = ".$value.$comma;
			}
		}

		$result = $this->db->query( $sql );

		if ( ! $result )
		{
			return false;
		}

		$item->setCopied( true );
		
		return $new_topic_id;

	}

	private function createNewId()
	{
		$id = md5( uniqid( rand(),true ) );

		return $id;
	}

	function setSourceSeminarId( $id )
	{
		$this->source_seminar_id = $id;
		$this->source_top_folder_id = md5( $id."top_folder" );
	}

	function setTargetSeminarId( $id )
	{
		$this->target_seminar_id = $id;
		$this->target_top_folder_id = md5( $id."top_folder" );
	}

}

?>