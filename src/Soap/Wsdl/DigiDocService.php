<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DiciDoc\Soap\Wsdl;

/**
 * Digital signature service
 *
 */
class DigiDocService extends \SoapClient
{
    /**
     *
     * @var array $classmap The defined classes
     * @access private
     */
    private static $classmap = array(
      'DataFileAttribute' => '\KG\DigiDoc\Soap\Wsdl\DataFileAttribute',
      'DataFileInfo' => '\KG\DigiDoc\Soap\Wsdl\DataFileInfo',
      'SignerRole' => '\KG\DigiDoc\Soap\Wsdl\SignerRole',
      'SignatureProductionPlace' => '\KG\DigiDoc\Soap\Wsdl\SignatureProductionPlace',
      'CertificatePolicy' => '\KG\DigiDoc\Soap\Wsdl\CertificatePolicy',
      'CertificateInfo' => '\KG\DigiDoc\Soap\Wsdl\CertificateInfo',
      'SignerInfo' => '\KG\DigiDoc\Soap\Wsdl\SignerInfo',
      'ConfirmationInfo' => '\KG\DigiDoc\Soap\Wsdl\ConfirmationInfo',
      'TstInfo' => '\KG\DigiDoc\Soap\Wsdl\TstInfo',
      'RevokedInfo' => '\KG\DigiDoc\Soap\Wsdl\RevokedInfo',
      'CRLInfo' => '\KG\DigiDoc\Soap\Wsdl\CRLInfo',
      'Error' => '\KG\DigiDoc\Soap\Wsdl\Error',
      'SignatureInfo' => '\KG\DigiDoc\Soap\Wsdl\SignatureInfo',
      'SignedDocInfo' => '\KG\DigiDoc\Soap\Wsdl\SignedDocInfo',
      'DataFileData' => '\KG\DigiDoc\Soap\Wsdl\DataFileData',
      'SignatureModule' => '\KG\DigiDoc\Soap\Wsdl\SignatureModule',
      'SignatureModulesArray' => '\KG\DigiDoc\Soap\Wsdl\SignatureModulesArray',
      'DataFileDigest' => '\KG\DigiDoc\Soap\Wsdl\DataFileDigest',
      'DataFileDigestList' => '\KG\DigiDoc\Soap\Wsdl\DataFileDigestList');

    /**
     *
     * @param array $options A array of config values
     * @param string $wsdl The wsdl file to use
     * @access public
     */
    public function __construct(array $options = array(), $wsdl = 'https://www.openxades.org:9443/?wsdl')
    {
        foreach (self::$classmap as $key => $value) {
            if (!isset($options['classmap'][$key])) {
                $options['classmap'][$key] = $value;
            }
        }

        parent::__construct($wsdl, $options);
    }

    /**
     * Service definition of function d__StartSession
     *
     * @param string $SigningProfile
     * @param string $SigDocXML
     * @param boolean $bHoldSession
     * @param DataFileData $datafile
     * @access public
     * @return list(string $Status, int $Sesscode, SignedDocInfo $SignedDocInfo)
     */
    public function StartSession($SigningProfile, $SigDocXML, $bHoldSession, DataFileData $datafile)
    {
        return $this->__soapCall('StartSession', array($SigningProfile, $SigDocXML, $bHoldSession, $datafile));
    }

    /**
     * Service definition of function d__CloseSession
     *
     * @param int $Sesscode
     * @access public
     * @return string
     */
    public function CloseSession($Sesscode)
    {
        return $this->__soapCall('CloseSession', array($Sesscode));
    }

    /**
     * Service definition of function d__CreateSignedDoc
     *
     * @param int $Sesscode
     * @param string $Format
     * @param string $Version
     * @access public
     * @return list(string $Status, SignedDocInfo $SignedDocInfo)
     */
    public function CreateSignedDoc($Sesscode, $Format, $Version)
    {
        return $this->__soapCall('CreateSignedDoc', array($Sesscode, $Format, $Version));
    }

    /**
     * Service definition of function d__AddDataFile
     *
     * @param int $Sesscode
     * @param string $FileName
     * @param string $MimeType
     * @param string $ContentType
     * @param int $Size
     * @param string $DigestType
     * @param string $DigestValue
     * @param string $Content
     * @access public
     * @return list(string $Status, SignedDocInfo $SignedDocInfo)
     */
    public function AddDataFile($Sesscode, $FileName, $MimeType, $ContentType, $Size, $DigestType, $DigestValue, $Content)
    {
        return $this->__soapCall('AddDataFile', array($Sesscode, $FileName, $MimeType, $ContentType, $Size, $DigestType, $DigestValue, $Content));
    }

    /**
     * Service definition of function d__RemoveDataFile
     *
     * @param int $Sesscode
     * @param string $DataFileId
     * @access public
     * @return list(string $Status, SignedDocInfo $SignedDocInfo)
     */
    public function RemoveDataFile($Sesscode, $DataFileId)
    {
        return $this->__soapCall('RemoveDataFile', array($Sesscode, $DataFileId));
    }

    /**
     * Service definition of function d__GetSignedDoc
     *
     * @param int $Sesscode
     * @access public
     * @return list(string $Status, string $SignedDocData)
     */
    public function GetSignedDoc($Sesscode)
    {
        return $this->__soapCall('GetSignedDoc', array($Sesscode));
    }

    /**
     * Service definition of function d__GetSignedDocInfo
     *
     * @param int $Sesscode
     * @access public
     * @return list(string $Status, SignedDocInfo $SignedDocInfo)
     */
    public function GetSignedDocInfo($Sesscode)
    {
        return $this->__soapCall('GetSignedDocInfo', array($Sesscode));
    }

    /**
     * Service definition of function d__GetDataFile
     *
     * @param int $Sesscode
     * @param string $DataFileId
     * @access public
     * @return list(string $Status, DataFileData $DataFileData)
     */
    public function GetDataFile($Sesscode, $DataFileId)
    {
        return $this->__soapCall('GetDataFile', array($Sesscode, $DataFileId));
    }

    /**
     * Service definition of function d__GetSignersCertificate
     *
     * @param int $Sesscode
     * @param string $SignatureId
     * @access public
     * @return list(string $Status, string $CertificateData)
     */
    public function GetSignersCertificate($Sesscode, $SignatureId)
    {
        return $this->__soapCall('GetSignersCertificate', array($Sesscode, $SignatureId));
    }

    /**
     * Service definition of function d__GetNotarysCertificate
     *
     * @param int $Sesscode
     * @param string $SignatureId
     * @access public
     * @return list(string $Status, string $CertificateData)
     */
    public function GetNotarysCertificate($Sesscode, $SignatureId)
    {
        return $this->__soapCall('GetNotarysCertificate', array($Sesscode, $SignatureId));
    }

    /**
     * Service definition of function d__GetNotary
     *
     * @param int $Sesscode
     * @param string $SignatureId
     * @access public
     * @return list(string $Status, string $OcspData)
     */
    public function GetNotary($Sesscode, $SignatureId)
    {
        return $this->__soapCall('GetNotary', array($Sesscode, $SignatureId));
    }

    /**
     * Service definition of function d__GetTSACertificate
     *
     * @param int $Sesscode
     * @param string $TimestampId
     * @access public
     * @return list(string $Status, string $CertificateData)
     */
    public function GetTSACertificate($Sesscode, $TimestampId)
    {
        return $this->__soapCall('GetTSACertificate', array($Sesscode, $TimestampId));
    }

    /**
     * Service definition of function d__GetTimestamp
     *
     * @param int $Sesscode
     * @param string $TimestampId
     * @access public
     * @return list(string $Status, string $TimestampData)
     */
    public function GetTimestamp($Sesscode, $TimestampId)
    {
        return $this->__soapCall('GetTimestamp', array($Sesscode, $TimestampId));
    }

    /**
     * Service definition of function d__GetCRL
     *
     * @param int $Sesscode
     * @param string $SignatureId
     * @access public
     * @return list(string $Status, string $CRLData)
     */
    public function GetCRL($Sesscode, $SignatureId)
    {
        return $this->__soapCall('GetCRL', array($Sesscode, $SignatureId));
    }

    /**
     * Service definition of function d__GetSignatureModules
     *
     * @param int $Sesscode
     * @param string $Platform
     * @param string $Phase
     * @param string $Type
     * @access public
     * @return list(string $Status, SignatureModulesArray $Modules)
     */
    public function GetSignatureModules($Sesscode, $Platform, $Phase, $Type)
    {
        return $this->__soapCall('GetSignatureModules', array($Sesscode, $Platform, $Phase, $Type));
    }

    /**
     * Service definition of function d__PrepareSignature
     *
     * @param int $Sesscode
     * @param string $SignersCertificate
     * @param string $SignersTokenId
     * @param string $Role
     * @param string $City
     * @param string $State
     * @param string $PostalCode
     * @param string $Country
     * @param string $SigningProfile
     * @access public
     * @return list(string $Status, string $SignatureId, string $SignedInfoDigest)
     */
    public function PrepareSignature($Sesscode, $SignersCertificate, $SignersTokenId, $Role, $City, $State, $PostalCode, $Country, $SigningProfile)
    {
        return $this->__soapCall('PrepareSignature', array($Sesscode, $SignersCertificate, $SignersTokenId, $Role, $City, $State, $PostalCode, $Country, $SigningProfile));
    }

    /**
     * Service definition of function d__FinalizeSignature
     *
     * @param int $Sesscode
     * @param string $SignatureId
     * @param string $SignatureValue
     * @access public
     * @return list(string $Status, SignedDocInfo $SignedDocInfo)
     */
    public function FinalizeSignature($Sesscode, $SignatureId, $SignatureValue)
    {
        return $this->__soapCall('FinalizeSignature', array($Sesscode, $SignatureId, $SignatureValue));
    }

    /**
     * Service definition of function d__RemoveSignature
     *
     * @param int $Sesscode
     * @param string $SignatureId
     * @access public
     * @return list(string $Status, SignedDocInfo $SignedDocInfo)
     */
    public function RemoveSignature($Sesscode, $SignatureId)
    {
        return $this->__soapCall('RemoveSignature', array($Sesscode, $SignatureId));
    }

    /**
     * Service definition of function d__GetVersion
     *
     * @access public
     * @return list(string $Name, string $Version, string $LibraryVersion)
     */
    public function GetVersion()
    {
        return $this->__soapCall('GetVersion', array());
    }

    /**
     * Service definition of function d__MobileSign
     *
     * @param int $Sesscode
     * @param string $SignerIDCode
     * @param string $SignersCountry
     * @param string $SignerPhoneNo
     * @param string $ServiceName
     * @param string $AdditionalDataToBeDisplayed
     * @param string $Language
     * @param string $Role
     * @param string $City
     * @param string $StateOrProvince
     * @param string $PostalCode
     * @param string $CountryName
     * @param string $SigningProfile
     * @param string $MessagingMode
     * @param int $AsyncConfiguration
     * @param boolean $ReturnDocInfo
     * @param boolean $ReturnDocData
     * @access public
     * @return list(string $Status, string $StatusCode, string $ChallengeID)
     */
    public function MobileSign($Sesscode, $SignerIDCode, $SignersCountry, $SignerPhoneNo, $ServiceName, $AdditionalDataToBeDisplayed, $Language, $Role, $City, $StateOrProvince, $PostalCode, $CountryName, $SigningProfile, $MessagingMode, $AsyncConfiguration, $ReturnDocInfo, $ReturnDocData)
    {
        return $this->__soapCall('MobileSign', array($Sesscode, $SignerIDCode, $SignersCountry, $SignerPhoneNo, $ServiceName, $AdditionalDataToBeDisplayed, $Language, $Role, $City, $StateOrProvince, $PostalCode, $CountryName, $SigningProfile, $MessagingMode, $AsyncConfiguration, $ReturnDocInfo, $ReturnDocData));
    }

    /**
     * Service definition of function d__GetStatusInfo
     *
     * @param int $Sesscode
     * @param boolean $ReturnDocInfo
     * @param boolean $WaitSignature
     * @access public
     * @return list(string $Status, string $StatusCode, SignedDocInfo $SignedDocInfo)
     */
    public function GetStatusInfo($Sesscode, $ReturnDocInfo, $WaitSignature)
    {
        return $this->__soapCall('GetStatusInfo', array($Sesscode, $ReturnDocInfo, $WaitSignature));
    }

    /**
     * Service definition of function d__MobileAuthenticate
     *
     * @param string $IDCode
     * @param string $CountryCode
     * @param string $PhoneNo
     * @param string $Language
     * @param string $ServiceName
     * @param string $MessageToDisplay
     * @param string $SPChallenge
     * @param string $MessagingMode
     * @param int $AsyncConfiguration
     * @param boolean $ReturnCertData
     * @param boolean $ReturnRevocationData
     * @access public
     * @return list(int $Sesscode, string $Status, string $UserIDCode, string $UserGivenname, string $UserSurname, string $UserCountry, string $UserCN, string $CertificateData, string $ChallengeID, string $Challenge, string $RevocationData)
     */
    public function MobileAuthenticate($IDCode, $CountryCode, $PhoneNo, $Language, $ServiceName, $MessageToDisplay, $SPChallenge, $MessagingMode, $AsyncConfiguration, $ReturnCertData, $ReturnRevocationData)
    {
        return $this->__soapCall('MobileAuthenticate', array($IDCode, $CountryCode, $PhoneNo, $Language, $ServiceName, $MessageToDisplay, $SPChallenge, $MessagingMode, $AsyncConfiguration, $ReturnCertData, $ReturnRevocationData));
    }

    /**
     * Service definition of function d__GetMobileAuthenticateStatus
     *
     * @param int $Sesscode
     * @param boolean $WaitSignature
     * @access public
     * @return list(string $Status, string $Signature)
     */
    public function GetMobileAuthenticateStatus($Sesscode, $WaitSignature)
    {
        return $this->__soapCall('GetMobileAuthenticateStatus', array($Sesscode, $WaitSignature));
    }

    /**
     * Service definition of function d__MobileCreateSignature
     *
     * @param string $IDCode
     * @param string $SignersCountry
     * @param string $PhoneNo
     * @param string $Language
     * @param string $ServiceName
     * @param string $MessageToDisplay
     * @param string $Role
     * @param string $City
     * @param string $StateOrProvince
     * @param string $PostalCode
     * @param string $CountryName
     * @param string $SigningProfile
     * @param DataFileDigestList $DataFiles
     * @param string $Format
     * @param string $Version
     * @param string $SignatureID
     * @param string $MessagingMode
     * @param int $AsyncConfiguration
     * @access public
     * @return list(int $Sesscode, string $ChallengeID, string $Status)
     */
    public function MobileCreateSignature($IDCode, $SignersCountry, $PhoneNo, $Language, $ServiceName, $MessageToDisplay, $Role, $City, $StateOrProvince, $PostalCode, $CountryName, $SigningProfile, DataFileDigestList $DataFiles, $Format, $Version, $SignatureID, $MessagingMode, $AsyncConfiguration)
    {
        return $this->__soapCall('MobileCreateSignature', array($IDCode, $SignersCountry, $PhoneNo, $Language, $ServiceName, $MessageToDisplay, $Role, $City, $StateOrProvince, $PostalCode, $CountryName, $SigningProfile, $DataFiles, $Format, $Version, $SignatureID, $MessagingMode, $AsyncConfiguration));
    }

    /**
     * Service definition of function d__GetMobileCreateSignatureStatus
     *
     * @param int $Sesscode
     * @param boolean $WaitSignature
     * @access public
     * @return list(int $Sesscode, string $Status, string $Signature)
     */
    public function GetMobileCreateSignatureStatus($Sesscode, $WaitSignature)
    {
        return $this->__soapCall('GetMobileCreateSignatureStatus', array($Sesscode, $WaitSignature));
    }

    /**
     * Service definition of function d__GetMobileCertificate
     *
     * @param string $IDCode
     * @param string $Country
     * @param string $PhoneNo
     * @param string $ReturnCertData
     * @access public
     * @return list(string $AuthCertStatus, string $SignCertStatus, string $AuthCertData, string $SignCertData)
     */
    public function GetMobileCertificate($IDCode, $Country, $PhoneNo, $ReturnCertData)
    {
        return $this->__soapCall('GetMobileCertificate', array($IDCode, $Country, $PhoneNo, $ReturnCertData));
    }

    /**
     * Service definition of function d__CheckCertificate
     *
     * @param string $Certificate
     * @param boolean $ReturnRevocationData
     * @access public
     * @return list(string $Status, string $UserIDCode, string $UserGivenname, string $UserSurname, string $UserCountry, string $UserOrganisation, string $UserCN, string $IssuerCN, string $KeyUsage, string $EnhancedKeyUsage, string $RevocationData)
     */
    public function CheckCertificate($Certificate, $ReturnRevocationData)
    {
        return $this->__soapCall('CheckCertificate', array($Certificate, $ReturnRevocationData));
    }
}
