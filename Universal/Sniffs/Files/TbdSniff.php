<?php
/**
 * PHPCSExtra, a collection of sniffs and standards for use with PHP_CodeSniffer.
 *
 * @package   PHPCSExtra
 * @copyright 2020 PHPCSExtra Contributors
 * @license   https://opensource.org/licenses/LGPL-3.0 LGPL3
 * @link      https://github.com/PHPCSStandards/PHPCSExtra
 */

namespace PHPCSExtra\Universal\Sniffs\Files;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use PHP_CodeSniffer\Sniffs\AbstractScopeSniff;

class TbdSniff extends AbstractScopeSniff
{

	/**
	 * Whether a specific OO token fo $ooOutsideScopeTokens has been encountered.
	 * 
	 * 	@var bool
	 */
	private $ooScopeSeen = false;

	/**
	 * Whether we have already scanned this file or not.
	 * 
	 * 	@var bool
	 */
	private $fileScanned = false;

	/**
	 * Current file being scanned.
	 * 
	 * @var string
	 */
	private $currentFile;

	/**
	 * Tokens we do NOT want to scan inside their scope.
	 * Note: Different from Tokens::$ooTokens because we are not accounting for anonymous classes.
	 * 
	 * @var array
	 */
	private $ooOutsideScopeTokens = [
		T_CLASS => T_CLASS,
		T_INTERFACE => T_INTERFACE,
		T_TRAIT => T_TRAIT
	];

    /**
     * Constructs a Squiz_Sniffs_Scope_MethodScopeSniff.
     */
    public function __construct()
    {
        parent::__construct(
			Tokens::$ooScopeTokens,
			[ T_FUNCTION ],
			true
		);

    }//end __construct()


    /**
     * Processes the function tokens within the class.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file where this token was found.
     * @param int                         $stackPtr  The position where the token was found.
     * @param int                         $currScope The current scope opener token.
     *
     * @return void
     */
    protected function processTokenWithinScope(File $phpcsFile, $stackPtr, $currScope)
    {
    }//end processTokenWithinScope()


    /**
     * Processes a token that is found within the scope that this test is
     * listening to.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file where this token was found.
     * @param int                         $stackPtr  The position in the stack where this
     *                                               token was found.
     *
     * @return void
     */
    protected function processTokenOutsideScope(File $phpcsFile, $stackPtr)
    {
		$fileName = $phpcsFile->getFilename();
		if ( $this->currentFile !== $fileName ) {
			$this->currentFile = $fileName;
			// Reset on new file.
			$this->fileScanned = false;
			$this->ooScopeSeen = false; 
		}

		if ( $this->fileScanned === false ) {
			$ooToken = $phpcsFile->findNext( 
				$this->ooOutsideScopeTokens,
				0, // Look in beginning for OO tokens.
				null,
				false
			);

			$tokens = $phpcsFile->getTokens();
			if ( isset( $this->ooOutsideScopeTokens[ $tokens[ $ooToken ]['code'] ] ) ) {
				// Found one! Set flag.
				$this->ooScopeSeen = true;
			}
			$this->fileScanned = true;
		}

		if ( $this->ooScopeSeen === true ) {
			$phpcsFile->addError(
				'Please do not mix OO and global functions within the same file.',
				$stackPtr, 
				'ooAndGlobalFound'
			);
		}

    }//end processTokenOutsideScope()


}//end class