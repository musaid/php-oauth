<?php

namespace OAuth;

class Scope
{
    private $_scope;

    public function __construct($scope)
    {
        if (NULL === $scope || empty($scope)) {
            // FIXME: we should have some default scope... maybe "default"
            $this->_scope = array();
        } else {
            if (is_array($scope)) {
                $scope = implode(" ", array_values($scope));
            }
            $this->_scope = $this->_normalizeScope($scope);
        }
    }

    private function _validateScope($scopeToTest)
    {
        //     scope       = scope-token *( SP scope-token )
        //     scope-token = 1*( %x21 / %x23-5B / %x5D-7E )
        $scopeToken = '(?:\x21|[\x23-\x5B]|[\x5D-\x7E])+';
        $scope = '/^' . $scopeToken . '(?:\x20' . $scopeToken . ')*$/';
        $result = preg_match($scope, $scopeToTest);

        return $result === 1;
    }

    private function _normalizeScope($scopeToNormalize)
    {
        if (!$this->_validateScope($scopeToNormalize)) {
            throw new ScopeException("invalid scope");
        }
        $scopeArray = explode(" ", $scopeToNormalize);
        // sort and remove duplicates
        sort($scopeArray, SORT_STRING);

        return array_values(array_unique($scopeArray, SORT_STRING));
    }

    public function getScope()
    {
        return implode(" ", $this->_scope);
    }

    public function getScopeAsArray()
    {
        return $this->_scope;
    }

    /**
     * This object scope needs to contain all the scopes from the provided
     * scope object.
     */
    public function hasScope(Scope $scope)
    {
        $s = $scope->getScopeAsArray();
        foreach ($s as $v) {
            if (!in_array($v, $this->_scope)) {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * This object scope needs to be a subset of the provided scope object.
     */
    public function isSubsetOf(Scope $scope)
    {
        $s = $scope->getScopeAsArray();
        foreach ($this->_scope as $v) {
            if (!in_array($v, $s)) {
                return FALSE;
            }
        }

        return TRUE;
    }

    public function mergeWith(Scope $scope)
    {
        $this->_scope = $this->_normalizeScope(implode(" ", array_merge($this->_scope, $scope->getScopeAsArray())));
    }

}
