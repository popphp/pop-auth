<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Auth\Adapter;

use Pop\Auth\Auth;
use Pop\Crypt;

/**
 * Auth abstract adapter class
 *
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.1.0
 */
abstract class EncryptedAdapter extends AbstractAdapter
{

    /**
     * Encryption method to use
     * @var int
     */
    protected $encryption = null;

    /**
     * Encryption options. Possible options are:
     *
     * 'salt'   // Custom Salt
     * 'secret' // Secret pepper
     *
     * 'cost'   // Bcrypt cost
     * 'prefix' // Bcrypt prefix
     *
     * 'rounds' // Sha rounds
     *
     * 'cipher' // Mcrypt cipher
     * 'mode'   // Mcrypt cipher
     * 'source' // Mcrypt source
     *
     * @var array
     */
    protected $encryptionOptions = [];

    /**
     * Method to set the encryption
     *
     * @param  int   $encryption
     * @param  array $options
     * @return AbstractAdapter
     */
    public function setEncryption($encryption = 0, array $options = [])
    {
        $enc = (int)$encryption;
        if (($enc >= 0) && ($enc <= 8)) {
            $this->encryption = $enc;
        }

        $this->setEncryptionOptions($options);

        return $this;
    }

    /**
     * Method to set the encryption options
     *
     * @param  array $options
     * @return AbstractAdapter
     */
    public function setEncryptionOptions(array $options = [])
    {
        $this->encryptionOptions = $options;
        return $this;
    }

    /**
     * Get encryption
     *
     * @return int
     */
    public function getEncryption()
    {
        return $this->encryption;
    }

    /**
     * Get encryption options
     *
     * @return array
     */
    public function getEncryptionOptions()
    {
        return $this->encryptionOptions;
    }

    /**
     * Method to verify password
     *
     * @param  string $hash
     * @param  string $attemptedPassword
     * @return boolean
     */
    public function verifyPassword($hash, $attemptedPassword)
    {
        $pw   = false;
        $salt = (!empty($this->encryptionOptions['salt'])) ? $this->encryptionOptions['salt'] : null;

        if (!empty($this->encryptionOptions['secret'])) {
            $attemptedPassword .= $this->encryptionOptions['secret'];
        }

        switch ($this->encryption) {
            case Auth::ENCRYPT_NONE:
                $pw = ($hash == $attemptedPassword);
                break;

            case Auth::ENCRYPT_MD5:
                $pw = ($hash == md5($attemptedPassword));
                break;

            case Auth::ENCRYPT_SHA1:
                $pw = ($hash == sha1($attemptedPassword));
                break;

            case Auth::ENCRYPT_CRYPT:
                $crypt = new Crypt\Crypt();
                $crypt->setSalt($salt);
                $pw = $crypt->verify($attemptedPassword, $hash);
                break;

            case Auth::ENCRYPT_BCRYPT:
                $crypt = new Crypt\Bcrypt();
                $crypt->setSalt($salt);

                // Set cost and prefix, if applicable
                if (!empty($this->encryptionOptions['cost'])) {
                    $crypt->setCost($this->encryptionOptions['cost']);
                }
                if (!empty($this->encryptionOptions['prefix'])) {
                    $crypt->setPrefix($this->encryptionOptions['prefix']);
                }

                $pw = $crypt->verify($attemptedPassword, $hash);
                break;

            case Auth::ENCRYPT_MCRYPT:
                $crypt = new Crypt\Mcrypt();
                $crypt->setSalt($salt);

                // Set cipher, mode and source, if applicable
                if (!empty($this->encryptionOptions['cipher'])) {
                    $crypt->setCipher($this->encryptionOptions['cipher']);
                }
                if (!empty($this->encryptionOptions['mode'])) {
                    $crypt->setMode($this->encryptionOptions['mode']);
                }
                if (!empty($this->encryptionOptions['source'])) {
                    $crypt->setSource($this->encryptionOptions['source']);
                }

                $pw = $crypt->verify($attemptedPassword, $hash);
                break;

            case Auth::ENCRYPT_CRYPT_MD5:
                $crypt = new Crypt\Md5();
                $crypt->setSalt($salt);
                $pw = $crypt->verify($attemptedPassword, $hash);
                break;

            case Auth::ENCRYPT_CRYPT_SHA_256:
                $crypt = new Crypt\Sha(256);
                $crypt->setSalt($salt);

                // Set rounds, if applicable
                if (!empty($this->encryptionOptions['rounds'])) {
                    $crypt->setRounds($this->encryptionOptions['rounds']);
                }

                $pw = $crypt->verify($attemptedPassword, $hash);
                break;

            case Auth::ENCRYPT_CRYPT_SHA_512:
                $crypt = new Crypt\Sha(512);
                $crypt->setSalt($salt);

                // Set rounds, if applicable
                if (!empty($this->encryptionOptions['rounds'])) {
                    $crypt->setRounds($this->encryptionOptions['rounds']);
                }

                $pw = $crypt->verify($attemptedPassword, $hash);
                break;
        }

        return $pw;
    }

}
