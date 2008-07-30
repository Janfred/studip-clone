<?
/*
* This class implements an tree to store the content data of an Stud.ip Seminar
* it stores download folders, user groups, connected user group folder,
* schedules, connected issues and folder connected to an issue and/or discussions connected to an issue
* also is stores common discussions
* uses by DataCopyTool as source to copy an Stud.ip Seminar
* Part of Alternative Copy Mechanism (ACM) 

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
//require_once("lib/phplib/db_mysql.inc");

class DataTree
{
	private $user_id;
	private $seminar_id;
	private $top_folder_id;
	private $db;
	private $db1;
	private $rootitem;

	private $doc_sql;
	private $folder_sql;
	private $seminar_sql;
	private $discussion_issue_sql;
	private $discussion_issue_child_sql;
	private $discussion_sql;
	private $discussion_child_sql;

	private $treeLevel;
	// php5 style constructor

	private $want_download_folder; //download folders and document structures
	private $want_folder_issue; // folders related to an schedule via themen and themen_termine / needs $want_schedules==true
	private $want_discussion_issue; //discussions related to an schedule via themen and themen_termine / needs $want_schedules==true
	private $want_schedules;  // schedules
	private	$want_status_group_folder; // folder related to a group of users
	private $want_status_group; // user group
	private $want_discussion;

	function __construct( $seminar_id, $user_id, $want_flags )
	{
		$this->top_folder_id = md5( $seminar_id."top_folder" );
		$this->seminar_id=$seminar_id;
		$this->user_id=$user_id;

		$this->rootitem = new Item(null,Item::ROOT );

		$this->init();

		$this->setWantFlags( $want_flags );
	}

	function init()
	{
		$this->folder_sql = "select * from folder where range_id = '";
		$this->issue_folder_sql = "select folder.* from themen,folder where themen.seminar_id='%s' and themen.issue_id = folder.range_id ";
		$this->doc_sql = "select * from dokumente where range_id = '";
		$this->discussion_issue_sql = "select px_topics.* from px_topics,themen where px_topics.Seminar_id = '%1\$s' and themen.issue_id = px_topics.topic_id and px_topics.topic_id = '%2\$s' and px_topics.parent_id = '0'";
		$this->discussion_issue_child_sql = "select px_topics.* from px_topics where px_topics.Seminar_id = '%2\$s' and px_topics.parent_id='%1\$s'";
		$this->discussion_sql = "select px_topics.* from  px_topics where px_topics.Seminar_id = '%1\$s' AND px_topics.parent_id='%2\$s' AND px_topics.topic_id  NOT IN";
		$this->discussion_sql = $this->discussion_sql." ( select px_topics.topic_id from px_topics,themen where themen.issue_id = px_topics.topic_id and px_topics.Seminar_id = '%1\$s' )";
		//$this->discussion_child_sql = "select * from px_topics where root_id = '%s'";
		$this->discussion_child_sql = "select * from px_topics where parent_id = '%1\$s' and Seminar_id = '%2\$s' and px_topics.topic_id  not in
(
    select issue_id from themen where seminar_id = '%2\$s'
)";
	}

	function setWantFlags( $want_flags )
	{
		$this->want_download_folder=false;;
		$this->want_folder_issue=false;
		$this->want_discussion_issue=false;
		$this->want_schedules=false;
		$this->want_status_group_folder=false;
		$this->want_status_group=false;
		$this->want_discussion=false;
		$this->want_issue_folder_as_download_folder=false;

		foreach( $want_flags as $key => $flag )
		{
			if ( property_exists($this,$key) )
			{
				$this->$key=$flag;
			}
		}

	}

	function buildTree()
	{

		if ( is_null( $this->db ) )
		{
			$this->db=new DB_Seminar;
			$this->db1=new DB_Seminar;
		}

		//common default folder (Allgemeiner Dateiordner)
		$sql = $this->folder_sql.$this->seminar_id."'";
		$db = $this->db;
		$resultArray = $this->fetch($db,$sql,"folder");

		$item = new Item( $this->rootitem, Item::DOWNLOAD_FOLDER );

		$item->setData( $resultArray[0],$resultArray["metadata"] );

		$this->buildSubTree( $item );

		//other download folders
		$sql = $this->folder_sql.$this->top_folder_id."'";
		$resultArray = $this->fetch($this->db,$sql,"folder");

		$numOfResults = $resultArray["num_results"];

		for ($i=0; $i<$numOfResults; $i++)
		{
			$item = new Item( $this->rootitem, Item::DOWNLOAD_FOLDER );
			$item->setData( $resultArray[$i],$resultArray["metadata"] );
			$this->buildSubTree( $item );
		}

		//add the issue_folders as download folders to the data_tree
		//this folders are not connected to an issue anymore (table: themen)
		if ($this->want_issue_folder_as_download_folder)
		{
			//if we want to copy the issue folders as download folders, 
			//we do not want to copy them a second time as issue_folders.
			$this->want_folder_issue=false;
			
			$sql = sprintf( $this->issue_folder_sql,$this->seminar_id );

			$resultArray = $this->fetch($this->db,$sql,"folder");

			$numOfResults = $resultArray["num_results"];

			for ($i=0; $i<$numOfResults; $i++)
			{
				$item = new Item( $this->rootitem, Item::DOWNLOAD_FOLDER );
				$item->setData( $resultArray[$i],$resultArray["metadata"] );
				$this->buildSubTree( $item );
			}
		}

		//statusgruppen folder
		if ( $this->want_status_group )
		{
			$sql =  "select * from statusgruppen where range_id = '".$this->seminar_id."'";

			$resultArray = $this->fetch($db,$sql,"statusgruppen");
			$numOfResults = $resultArray["num_results"];

			for ($i=0; $i<$numOfResults; $i++)
			{
				$range_id =  $resultArray[$i]["statusgruppe_id"];

				// contains the connected statusgruppe data
				$groupItem = new Item( $this->rootitem, Item::GROUP_DATA  );
				$groupItem->setData( $resultArray[$i],$resultArray["metadata"] );

				if ($this->want_status_group_folder )
				{
					$sql = $this->folder_sql.$range_id."'";
					$resultArray1 = $this->fetch($this->db,$sql,"folder");

					if ( $resultArray1 ) // not every status group has an folder but if it has, it's only one
					{
						//$numOfResults1 = $resultArray1["num_results"];

						$folderItem = new Item( $groupItem, Item::GROUP_FOLDER );
						$folderItem->setData( $resultArray1[0],$resultArray1["metadata"] );

						$this->buildSubTree( $folderItem );
					}
				}

			}
		}

		// schedules,issues,issuefolder ( tables: termine,themen,themen_termine,folder )
		if ( $this->want_schedules)
		{
			$sql = "select * from termine where range_id ='".$this->seminar_id."'";

			$resultArray = $this->fetch($db,$sql,"termine");
			$numOfResults = $resultArray["num_results"];

			for ($i=0; $i<$numOfResults; $i++)
			{

				//add a SCHEDULE item to the seminar data tree
				$scheduleItem = new Item ( $this->rootitem, Item::SCHEDULE  );
				$scheduleItem->setData( $resultArray[$i],$resultArray["metadata"] );

				$termin_id = $resultArray[$i]["termin_id"];

				if ( $this->want_folder_issue || $this->want_discussion_issue )
				{

					// look, if the schedule is related to an Issue
					$sql = "select themen.* from termine,themen_termine,themen where termine.termin_id = themen_termine.termin_id and ";
					$sql = $sql."themen.issue_id = themen_termine.issue_id and termine.termin_id ='".$termin_id."'";

					$resultArray1 = $this->fetch($this->db,$sql,"themen");

					if ( $resultArray1 )
					{
						//$gotRelation=false;
						// the issue item has the schedule item as parent because it is related to it
						$issueItem = new Item( $scheduleItem, Item::ISSUE_DATA );
						$issueItem->setData( $resultArray1[$i],$resultArray1["metadata"] );

						$issue_id = $resultArray1[0]["issue_id"];  //in this case themen.issue_id == folder.range_id

						if ( $this->want_folder_issue )
						{
							$sql = $this->folder_sql.$issue_id."'";
							$resultArray2 = $this->fetch($this->db,$sql,"folder");

							if ( $resultArray2 )  // does the issue item has an issue folder ?
							{
								$folderItem = new Item( $issueItem, Item::ISSUE_FOLDER ); //folder item is parent of issue item
								$folderItem->setData( $resultArray2[0],$resultArray2["metadata"] );
								//$gotRelation=true;
							}
						}

						if ( $this->want_discussion_issue)
						{
							//$sql = $this->discussion_sql.$issue_id."'";
							$sql = sprintf( $this->discussion_issue_sql,$this->seminar_id,$issue_id );
							$resultArray3 = $this->fetch($this->db,$sql,"px_topics");

							if ( $resultArray3 )  // does the issue item has an discussion ?
							{
								$discussionItem = new Item( $issueItem, Item::ISSUE_DISCUSSION );
								$discussionItem->setData( $resultArray3[0],$resultArray3["metadata"] );
								//$gotRelation=true;
								$this->buildDiscussionIssueSubTree( $discussionItem );
							}
						}
					}
				}
			}
		}

		//discussions which are not related to an issue ( px_topics.topic_id != themen.issue_id )
		if( $this->want_discussion )
		{
			$sql = sprintf( $this->discussion_sql,$this->seminar_id,"0" ); // sql for root isue ( not related to themen )

			$resultArray = $this->fetch($db,$sql,"px_topics");
			$numOfResults = $resultArray["num_results"];
			for ($i=0; $i<$numOfResults; $i++)
			{
				$discussionItem = new Item( $this->rootitem, Item::DISCUSSION ); //folder item is parent of issue item
				$discussionItem->setData( $resultArray[$i],$resultArray["metadata"] );

				$this->buildDiscussionSubTree( $discussionItem );
			}
		}

	}


	//build an item subtree (for discussions trees connected to an issue via the tree root item )
	//recursive function
	private function buildDiscussionIssueSubTree( Item $parentItem  )
	{
		$db = $this->db;
		$parent_id = $parentItem->getFieldValue( "topic_id" );

		$sql = sprintf($this->discussion_issue_child_sql,$parent_id,$this->seminar_id );

		$resultArray = $this->fetch($db,$sql,"px_topics");
		$numOfResults = $resultArray["num_results"];

		for ($i=0; $i<$numOfResults; $i++)
		{
			$discussionChildItem = new Item( $parentItem, Item::DISCUSSION );
			$discussionChildItem->setData( $resultArray[$i],$resultArray["metadata"] );

			$this->buildDiscussionIssueSubTree( $discussionChildItem );
		}
	}

	//build an item subtree (for common discussion trees)
	//recursive function
	private function buildDiscussionSubTree( Item $parentItem  )
	{
		$db = $this->db;
		$parent_id = $parentItem->getFieldValue( "topic_id" );

		$sql = sprintf($this->discussion_child_sql,$parent_id,$this->seminar_id );

		$resultArray = $this->fetch($db,$sql,"px_topics");
		$numOfResults = $resultArray["num_results"];

		for ($i=0; $i<$numOfResults; $i++)
		{
			$discussionChildItem = new Item( $parentItem, Item::DISCUSSION );
			$discussionChildItem->setData( $resultArray[$i],$resultArray["metadata"] );

			$this->buildDiscussionSubTree( $discussionChildItem );
		}
	}

	//build an item subtree (for folder)
	//recursive function
	private function buildSubTree( Item $parentItem )
	{

		$db = $this->db;

		if ( $parentItem->getItemType() == Item::ROOT )
		{
			$folder_id = $this->seminar_id;
		}
		else
		{
			$folder_id = $parentItem->getFieldValue("folder_id");
		}
		$sql = $this->folder_sql.$folder_id."'";
		$resultArray = $this->fetch($db,$sql,"folder");

		$numOfResults = $resultArray["num_results"];
		for ($i=0; $i<$numOfResults; $i++)
		{
			$item = new Item( $parentItem, Item::DOWNLOAD_FOLDER );
			$item->setData( $resultArray[$i],$resultArray["metadata"] );
			$this->buildSubTree( $item );

		}
		// documents in the current folder
		$sql = $this->doc_sql.$folder_id."'";

		$resultArray = $this->fetch($db,$sql,"dokumente");

		$numOfResults = $resultArray["num_results"];
		for ($i=0; $i<$numOfResults; $i++)
		{
			$item = new Item( $parentItem, Item::DOCUMENT  );
			$item->setData( $resultArray[$i],$resultArray["metadata"] );
			$url = $item->getFieldValue( "url" );
			if( $url )
			{
				$item->setItemType( Item::HYPERLINK );
			}
		}
	}

	// function to fetch data with select statements
	private function fetch( &$db, $sql, $table=NULL )
	{
		//$num_of_fields=0; // number of fields of the current table
		$metadata=null; // metadata array of the current table
		$resultArray=null; // array for result arrays

		$queryId = $db->query( $sql );
		if ( ! $queryId )
		{
			$message = "Problem with mysql query in function DataTree->fetch \n";
			$message = $message."SQL: ".$sql;
			throw new Exception( $message );
		}

		$values = $db->next_record();

		if (! $values )
		{
			return false;
		}

		$resultCount = 0;
		while ( $values )
		{
			$resultCount++;

			$resultArray[] = $db->Record;

			$values = $db->next_record();

		}

		if( $table )
		{
			$metadata = $db->metadata( $table,true );
			if ( ! $metadata )
			{
				$message = "Problem getting metadata from ".$table." in function DataTree->fetch ";
				throw new Exception( $message );
			}

			$resultArray["metadata"] = $metadata;
		}

		$resultArray["num_results"]=$resultCount;
		return $resultArray;
	}

	function getRootItem()
	{
		return $this->rootitem;
	}

	// just for testing purposes
	function showSubTree( Item $folderItem )
	{
		$offset="";
		for ($i=0;$i<$this->treeLevel;$i++)
		{
			$offset = $offset."&nbsp;&nbsp;";
		}
		$children = $folderItem->getChildren();

		$numOfChildren = $folderItem->getNumOfChildren();

		for($i=0;$i<$numOfChildren;$i++)
		{
			//$child = new Item();
			//$child->getItemType();
			$child = $children[$i];
			if( $child->isFolder() )
			{
				echo $offset."Folder: ".$child->getFieldValue("name");
				echo "<br>";
				$this->treeLevel=$this->treeLevel+1;
				$this->showSubTree( $child );
				$this->treeLevel=$this->treeLevel-1;

			}
			else if ( $child->getItemType() == Item::DOCUMENT  )
			{
				echo $offset."&nbsp;&nbsp;Document: ".$child->getFieldValue("name");
				echo " ".$child->getFieldValue("filename");
				echo "<br>";
			}
			else if ( $child->getItemType() == Item::HYPERLINK )
			{
				echo $offset."&nbsp;&nbsp;Hyperlink: ".$child->getFieldValue("name");
				echo " ".$child->getFieldValue("filename");
				echo " ".$child->getFieldValue("url");
				echo "<br>";
			}
		}
	}
}

?>