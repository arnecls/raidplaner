<?php
    require_once(dirname(__FILE__)."/out.class.php");

    class Query
    {
        public $PDO = null;
        private $AffectedRows;
        private $OutputHTML;

        // --------------------------------------------------------------------------------------------

        public function __construct($aPDOStatement)
        {
            $this->PDO = $aPDOStatement;
            $this->AffectedRows = 0;
            $this->OutputHTML = false;
        }

        public function setErrorsAsHTML($aEnable)
        {
            $this->OutputHTML = $aEnable;
        }

        // --------------------------------------------------------------------------------------------

        public function bindValue( $aName, $aValue, $aType )
        {
            return $this->PDO->bindValue($aName, $aValue, $aType);
        }

        // --------------------------------------------------------------------------------------------

        public function bindParam( $aName, $aValue, $aType )
        {
            return $this->PDO->bindParam($aName, $aValue, $aType);
        }

        // --------------------------------------------------------------------------------------------

        public function execute($aThrow=false)
        {
            if ($this->PDO->execute() === false)
            {
                $this->AffectedRows = 0;
                $this->PDO->closeCursor();

                if ($aThrow)
                    $this->throwError($this->PDO);

                $this->postErrorMessage($this->PDO);
                return false;
            }

            $this->AffectedRows = $this->PDO->rowCount();
            $this->PDO->closeCursor();
            return true;
        }

        // --------------------------------------------------------------------------------------------

        public function fetchFirst($aThrow=false)
        {
            if ($this->PDO->execute() === false)
            {
                $this->AffectedRows = 0;
                $this->PDO->closeCursor();

                if ($aThrow)
                    $this->throwError($this->PDO);

                $this->postErrorMessage($this->PDO);
                return null;
            }

            $this->AffectedRows = $this->PDO->rowCount();

            $Data = ($this->AffectedRows > 0)

                ? $this->PDO->fetch(PDO::FETCH_ASSOC)
                : null;

            $this->PDO->closeCursor();

            return $Data;
        }

        // --------------------------------------------------------------------------------------------

        public function fetchFirstOfLoop($aThrow=false)
        {
            if ($this->PDO->execute() === false)
            {
                $this->AffectedRows = 0;
                $this->PDO->closeCursor();

                if ($aThrow)
                    $this->throwError($this->PDO);

                $this->postErrorMessage($this->PDO);
                return null;
            }

            $this->AffectedRows = $this->PDO->rowCount();
            $Data = $this->PDO->fetch(PDO::FETCH_ASSOC);
            return $Data;
        }

        // --------------------------------------------------------------------------------------------

        public function loop($aFunction, $aThrow=false)
        {
            if ($this->PDO->execute() === false)
            {
                $this->AffectedRows = 0;
                $this->PDO->closeCursor();

                if ($aThrow)
                    $this->throwError($this->PDO);

                $this->postErrorMessage($this->PDO);
                return 0;
            }

            $this->AffectedRows = $this->PDO->rowCount();

            $RowCount = 0;
            while($Data = $this->PDO->fetch(PDO::FETCH_ASSOC))
            {
                ++$RowCount;
                if ($aFunction($Data) === false)
                    break;
            }

            $this->PDO->closeCursor();
            return $RowCount;
        }

        // --------------------------------------------------------------------------------------------

        public function getAffectedRows()
        {
            return $this->AffectedRows;
        }

        // --------------------------------------------------------------------------------------------

        public function throwError()
        {
            $Message = L("DatabaseError").": ";
            $ErrorInfo = $this->PDO->errorInfo();

            foreach($ErrorInfo as $Info)
            {
                $Message .= $Info.", ";
            }

            throw new PDOException($Message);
        }

        // --------------------------------------------------------------------------------------------

        public function postErrorMessage()
        {
            if ($this->OutputHTML)
            {
                $this->postHTMLErrorMessage();
            }
            else
            {
                $Out = Out::getInstance();
                $Out->pushError(L("DatabaseError"));

                $ErrorInfo = $this->PDO->errorInfo();

                foreach($ErrorInfo as $Info)
                {
                    $Out->pushError($Info);
                }
            }
        }

        // --------------------------------------------------------------------------------------------

        public function postHTMLErrorMessage()
        {
            $ErrorInfo = $this->PDO->errorInfo();
            echo "<div class=\"database_error\">";
            echo "<div class=\"error_head\">".L("DatabaseError")."</div>";
            echo "<div class=\"error_line error_line1\">".$ErrorInfo[0]."</div>";
            echo "<div class=\"error_line error_line2\">".$ErrorInfo[2]."</div>";
            echo "</div>";
        }
    }

?>