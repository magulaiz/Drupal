<?php

declare(strict_types=1);

namespace Drupal\Core\File\MimeType;

use FileEye\MimeMap\Map\AbstractMap;

/**
 * Class for mapping file extensions to MIME types.
 *
 * This file is generated using:
 * vendor/bin/fileeye-mimemap update --class
 * '\Drupal\Core\File\MimeType\FileEyeMimeTypeMap'
 *
 * Do not edit the map manually.
 */
class FileEyeMimeTypeMap extends AbstractMap {

  /**
   * {@inheritdoc}
   */
  protected static $instance;

  /**
   * {@inheritdoc}
   */
  public function getFileName(): string {
    return __FILE__;
  }

  /**
   * Mapping between file extensions and MIME types.
   *
   * The array has three main keys, 't' that stores MIME types, 'e' that map
   * file extensions to MIME types, and 'a' that store MIME type aliases.
   *
   * The entire map is created automatically by running
   *  $ fileye-mimemap update [URL] [YAML] [FILE]
   * on the command line.
   * The utility application fetches the map from the Apache HTTPD
   * documentation website, and integrates its definitions with any further
   * specifications contained in the YAML file.
   *
   * DO NOT CHANGE THE MAPPING ARRAY MANUALLY.
   *
   * @var array<string, array<int|string, array<string, array<int,string>>>>
   * @internal
   *
   */
  // phpcs:disable
  protected static $map = [
    't' =>
      [
        'application/andrew-inset' =>
          [
            'desc' =>
              [
                0 => 'ATK inset',
                1 => 'ATK: Andrew Toolkit',
              ],
            'e' =>
              [
                0 => 'ez',
              ],
          ],
        'application/annodex' =>
          [
            'a' =>
              [
                0 => 'application/x-annodex',
              ],
            'desc' =>
              [
                0 => 'Annodex exchange format',
              ],
            'e' =>
              [
                0 => 'anx',
              ],
          ],
        'application/appinstaller' =>
          [
            'desc' =>
              [
                0 => 'Windows app store installer',
              ],
            'e' =>
              [
                0 => 'appinstaller',
              ],
          ],
        'application/applixware' =>
          [
            'e' =>
              [
                0 => 'aw',
              ],
          ],
        'application/appx' =>
          [
            'desc' =>
              [
                0 => 'Windows app store package',
              ],
            'e' =>
              [
                0 => 'appx',
              ],
          ],
        'application/appxbundle' =>
          [
            'desc' =>
              [
                0 => 'Windows app store bundle',
              ],
            'e' =>
              [
                0 => 'appxbundle',
              ],
          ],
        'application/atom+xml' =>
          [
            'desc' =>
              [
                0 => 'Atom syndication feed',
              ],
            'e' =>
              [
                0 => 'atom',
              ],
          ],
        'application/atomcat+xml' =>
          [
            'e' =>
              [
                0 => 'atomcat',
              ],
          ],
        'application/atomsvc+xml' =>
          [
            'e' =>
              [
                0 => 'atomsvc',
              ],
          ],
        'application/cbor' =>
          [
            'desc' =>
              [
                0 => 'CBOR Data',
                1 => 'CBOR: Concise Binary Object Representation',
              ],
            'e' =>
              [
                0 => 'cbor',
              ],
          ],
        'application/ccxml+xml' =>
          [
            'e' =>
              [
                0 => 'ccxml',
              ],
          ],
        'application/cdmi-capability' =>
          [
            'e' =>
              [
                0 => 'cdmia',
              ],
          ],
        'application/cdmi-container' =>
          [
            'e' =>
              [
                0 => 'cdmic',
              ],
          ],
        'application/cdmi-domain' =>
          [
            'e' =>
              [
                0 => 'cdmid',
              ],
          ],
        'application/cdmi-object' =>
          [
            'e' =>
              [
                0 => 'cdmio',
              ],
          ],
        'application/cdmi-queue' =>
          [
            'e' =>
              [
                0 => 'cdmiq',
              ],
          ],
        'application/cu-seeme' =>
          [
            'e' =>
              [
                0 => 'cu',
              ],
          ],
        'application/davmount+xml' =>
          [
            'e' =>
              [
                0 => 'davmount',
              ],
          ],
        'application/dicom' =>
          [
            'desc' =>
              [
                0 => 'DICOM image',
                1 => 'DICOM: Digital Imaging and Communications in Medicine',
              ],
            'e' =>
              [
                0 => 'dcm',
              ],
          ],
        'application/docbook+xml' =>
          [
            'a' =>
              [
                0 => 'application/x-docbook+xml',
                1 => 'application/vnd.oasis.docbook+xml',
              ],
            'desc' =>
              [
                0 => 'DocBook document',
              ],
            'e' =>
              [
                0 => 'dbk',
                1 => 'docbook',
              ],
          ],
        'application/dssc+der' =>
          [
            'e' =>
              [
                0 => 'dssc',
              ],
          ],
        'application/dssc+xml' =>
          [
            'e' =>
              [
                0 => 'xdssc',
              ],
          ],
        'application/ecmascript' =>
          [
            'a' =>
              [
                0 => 'text/ecmascript',
              ],
            'desc' =>
              [
                0 => 'ECMAScript program',
              ],
            'e' =>
              [
                0 => 'ecma',
                1 => 'es',
              ],
          ],
        'application/emma+xml' =>
          [
            'e' =>
              [
                0 => 'emma',
              ],
          ],
        'application/epub+zip' =>
          [
            'desc' =>
              [
                0 => 'Electronic book document',
              ],
            'e' =>
              [
                0 => 'epub',
              ],
          ],
        'application/exi' =>
          [
            'e' =>
              [
                0 => 'exi',
              ],
          ],
        'application/fits' =>
          [
            'a' =>
              [
                0 => 'image/x-fits',
                1 => 'image/fits',
              ],
            'desc' =>
              [
                0 => 'FITS document',
                1 => 'FITS: Flexible Image Transport System',
              ],
            'e' =>
              [
                0 => 'fits',
                1 => 'fit',
                2 => 'fts',
              ],
          ],
        'application/font-tdpfr' =>
          [
            'a' =>
              [
                0 => 'application/vnd.truedoc',
              ],
            'desc' =>
              [
                0 => 'TDPFR font',
                1 => 'TDPFR: TrueDoc Portable Font Resource',
              ],
            'e' =>
              [
                0 => 'pfr',
              ],
          ],
        'application/geo+json' =>
          [
            'a' =>
              [
                0 => 'application/vnd.geo+json',
              ],
            'desc' =>
              [
                0 => 'GeoJSON geospatial data',
              ],
            'e' =>
              [
                0 => 'geojson',
                1 => 'geo.json',
              ],
          ],
        'application/gml+xml' =>
          [
            'desc' =>
              [
                0 => 'GML document',
                1 => 'GML: Geography Markup Language',
              ],
            'e' =>
              [
                0 => 'gml',
              ],
          ],
        'application/gnunet-directory' =>
          [
            'desc' =>
              [
                0 => 'GNUnet search file',
              ],
            'e' =>
              [
                0 => 'gnd',
              ],
          ],
        'application/gpx+xml' =>
          [
            'a' =>
              [
                0 => 'application/gpx',
                1 => 'application/x-gpx+xml',
                2 => 'application/x-gpx',
              ],
            'desc' =>
              [
                0 => 'GPX geographic data',
                1 => 'GPX: GPS Exchange Format',
              ],
            'e' =>
              [
                0 => 'gpx',
              ],
          ],
        'application/gxf' =>
          [
            'e' =>
              [
                0 => 'gxf',
              ],
          ],
        'application/gzip' =>
          [
            'a' =>
              [
                0 => 'application/x-gzip',
              ],
            'desc' =>
              [
                0 => 'Gzip archive',
              ],
            'e' =>
              [
                0 => 'gz',
              ],
          ],
        'application/hta' =>
          [
            'desc' =>
              [
                0 => 'Windows HTML desktop application',
                1 => 'HTML: HyperText Markup Language',
              ],
            'e' =>
              [
                0 => 'hta',
              ],
          ],
        'application/hyperstudio' =>
          [
            'e' =>
              [
                0 => 'stk',
              ],
          ],
        'application/illustrator' =>
          [
            'a' =>
              [
                0 => 'application/vnd.adobe.illustrator',
              ],
            'desc' =>
              [
                0 => 'Adobe Illustrator document',
              ],
            'e' =>
              [
                0 => 'ai',
              ],
          ],
        'application/inkml+xml' =>
          [
            'e' =>
              [
                0 => 'ink',
                1 => 'inkml',
              ],
          ],
        'application/ipfix' =>
          [
            'e' =>
              [
                0 => 'ipfix',
              ],
          ],
        'application/its+xml' =>
          [
            'desc' =>
              [
                0 => 'ITS translation file',
                1 => 'ITS: Internationalization Tag Set',
              ],
            'e' =>
              [
                0 => 'its',
              ],
          ],
        'application/java-archive' =>
          [
            'a' =>
              [
                0 => 'application/x-jar',
                1 => 'application/x-java-archive',
              ],
            'desc' =>
              [
                0 => 'Java archive',
              ],
            'e' =>
              [
                0 => 'jar',
              ],
          ],
        'application/java-serialized-object' =>
          [
            'e' =>
              [
                0 => 'ser',
              ],
          ],
        'application/jrd+json' =>
          [
            'desc' =>
              [
                0 => 'JRD document',
                1 => 'JRD: JSON Resource Descriptor',
              ],
            'e' =>
              [
                0 => 'jrd',
              ],
          ],
        'application/json' =>
          [
            'desc' =>
              [
                0 => 'JSON document',
                1 => 'JSON: JavaScript Object Notation',
              ],
            'e' =>
              [
                0 => 'json',
              ],
          ],
        'application/json-patch+json' =>
          [
            'desc' =>
              [
                0 => 'JSON patch',
                1 => 'JSON: JavaScript Object Notation',
              ],
            'e' =>
              [
                0 => 'json-patch',
              ],
          ],
        'application/json5' =>
          [
            'desc' =>
              [
                0 => 'JSON5 document',
                1 => 'JSON5: JavaScript Object Notation 5',
              ],
            'e' =>
              [
                0 => 'json5',
              ],
          ],
        'application/jsonml+json' =>
          [
            'e' =>
              [
                0 => 'jsonml',
              ],
          ],
        'application/ld+json' =>
          [
            'desc' =>
              [
                0 => 'JSON-LD document',
                1 => 'JSON-LD: JavaScript Object Notation for Linked Data',
              ],
            'e' =>
              [
                0 => 'jsonld',
              ],
          ],
        'application/lost+xml' =>
          [
            'e' =>
              [
                0 => 'lostxml',
              ],
          ],
        'application/mac-binhex40' =>
          [
            'desc' =>
              [
                0 => 'Macintosh BinHex-encoded file',
              ],
            'e' =>
              [
                0 => 'hqx',
              ],
          ],
        'application/mac-compactpro' =>
          [
            'e' =>
              [
                0 => 'cpt',
              ],
          ],
        'application/mads+xml' =>
          [
            'e' =>
              [
                0 => 'mads',
              ],
          ],
        'application/marc' =>
          [
            'e' =>
              [
                0 => 'mrc',
              ],
          ],
        'application/marcxml+xml' =>
          [
            'e' =>
              [
                0 => 'mrcx',
              ],
          ],
        'application/mathematica' =>
          [
            'a' =>
              [
                0 => 'application/x-mathematica',
              ],
            'desc' =>
              [
                0 => 'Mathematica Notebook file',
              ],
            'e' =>
              [
                0 => 'ma',
                1 => 'nb',
                2 => 'mb',
              ],
          ],
        'application/mathml+xml' =>
          [
            'a' =>
              [
                0 => 'text/mathml',
              ],
            'desc' =>
              [
                0 => 'MathML document',
                1 => 'MathML: Mathematical Markup Language',
              ],
            'e' =>
              [
                0 => 'mathml',
                1 => 'mml',
              ],
          ],
        'application/mbox' =>
          [
            'desc' =>
              [
                0 => 'Mailbox file',
              ],
            'e' =>
              [
                0 => 'mbox',
              ],
          ],
        'application/mediaservercontrol+xml' =>
          [
            'e' =>
              [
                0 => 'mscml',
              ],
          ],
        'application/metalink+xml' =>
          [
            'desc' =>
              [
                0 => 'Metalink file',
              ],
            'e' =>
              [
                0 => 'metalink',
              ],
          ],
        'application/metalink4+xml' =>
          [
            'desc' =>
              [
                0 => 'Metalink file',
              ],
            'e' =>
              [
                0 => 'meta4',
              ],
          ],
        'application/mets+xml' =>
          [
            'e' =>
              [
                0 => 'mets',
              ],
          ],
        'application/microsoftpatch' =>
          [
            'desc' =>
              [
                0 => 'Windows Installer patch',
              ],
            'e' =>
              [
                0 => 'msp',
              ],
          ],
        'application/microsoftupdate' =>
          [
            'desc' =>
              [
                0 => 'Windows Update package',
              ],
            'e' =>
              [
                0 => 'msu',
              ],
          ],
        'application/mods+xml' =>
          [
            'e' =>
              [
                0 => 'mods',
              ],
          ],
        'application/mp21' =>
          [
            'e' =>
              [
                0 => 'm21',
                1 => 'mp21',
              ],
          ],
        'application/mp4' =>
          [
            'e' =>
              [
                0 => 'mp4s',
              ],
          ],
        'application/msix' =>
          [
            'desc' =>
              [
                0 => 'Windows app store package',
              ],
            'e' =>
              [
                0 => 'msix',
              ],
          ],
        'application/msixbundle' =>
          [
            'desc' =>
              [
                0 => 'Windows app store bundle',
              ],
            'e' =>
              [
                0 => 'msixbundle',
              ],
          ],
        'application/msword' =>
          [
            'a' =>
              [
                0 => 'application/vnd.ms-word',
                1 => 'application/x-msword',
                2 => 'zz-application/zz-winassoc-doc',
              ],
            'desc' =>
              [
                0 => 'Word document',
              ],
            'e' =>
              [
                0 => 'doc',
                1 => 'dot',
              ],
          ],
        'application/msword-template' =>
          [
            'desc' =>
              [
                0 => 'Word template',
              ],
            'e' =>
              [
                0 => 'dot',
              ],
          ],
        'application/mxf' =>
          [
            'desc' =>
              [
                0 => 'MXF video',
                1 => 'MXF: Material Exchange Format',
              ],
            'e' =>
              [
                0 => 'mxf',
              ],
          ],
        'application/octet-stream' =>
          [
            'e' =>
              [
                0 => 'bin',
                1 => 'dms',
                2 => 'lrf',
                3 => 'mar',
                4 => 'so',
                5 => 'dist',
                6 => 'distz',
                7 => 'pkg',
                8 => 'bpk',
                9 => 'dump',
                10 => 'elc',
                11 => 'deploy',
              ],
          ],
        'application/oda' =>
          [
            'desc' =>
              [
                0 => 'ODA document',
                1 => 'ODA: Office Document Architecture',
              ],
            'e' =>
              [
                0 => 'oda',
              ],
          ],
        'application/oebps-package+xml' =>
          [
            'e' =>
              [
                0 => 'opf',
              ],
          ],
        'application/ogg' =>
          [
            'a' =>
              [
                0 => 'application/x-ogg',
              ],
            'desc' =>
              [
                0 => 'Ogg multimedia file',
              ],
            'e' =>
              [
                0 => 'ogx',
              ],
          ],
        'application/omdoc+xml' =>
          [
            'e' =>
              [
                0 => 'omdoc',
              ],
          ],
        'application/onenote' =>
          [
            'e' =>
              [
                0 => 'onetoc',
                1 => 'onetoc2',
                2 => 'onetmp',
                3 => 'onepkg',
              ],
          ],
        'application/ovf' =>
          [
            'a' =>
              [
                0 => 'application/x-virtualbox-ova',
              ],
            'desc' =>
              [
                0 => 'OVF disk image',
                1 => 'OVF: Open Virtualization Format',
              ],
            'e' =>
              [
                0 => 'ova',
              ],
          ],
        'application/owl+xml' =>
          [
            'desc' =>
              [
                0 => 'OWL XML file',
                1 => 'OWL: Web Ontology Language',
              ],
            'e' =>
              [
                0 => 'owx',
              ],
          ],
        'application/oxps' =>
          [
            'desc' =>
              [
                0 => 'OpenXPS document',
                1 => 'OpenXPS: Open XML Paper Specification',
              ],
            'e' =>
              [
                0 => 'oxps',
              ],
          ],
        'application/patch-ops-error+xml' =>
          [
            'e' =>
              [
                0 => 'xer',
              ],
          ],
        'application/pdf' =>
          [
            'a' =>
              [
                0 => 'application/x-pdf',
                1 => 'image/pdf',
                2 => 'application/acrobat',
                3 => 'application/nappdf',
              ],
            'desc' =>
              [
                0 => 'PDF document',
                1 => 'PDF: Portable Document Format',
              ],
            'e' =>
              [
                0 => 'pdf',
              ],
          ],
        'application/pgp-encrypted' =>
          [
            'a' =>
              [
                0 => 'application/pgp',
              ],
            'desc' =>
              [
                0 => 'PGP/MIME-encrypted message header',
              ],
            'e' =>
              [
                0 => 'pgp',
                1 => 'gpg',
                2 => 'asc',
              ],
          ],
        'application/pgp-keys' =>
          [
            'desc' =>
              [
                0 => 'PGP keys',
                1 => 'PGP: Pretty Good Privacy',
              ],
            'e' =>
              [
                0 => 'skr',
                1 => 'pkr',
                2 => 'asc',
                3 => 'pgp',
                4 => 'gpg',
                5 => 'key',
              ],
          ],
        'application/pgp-signature' =>
          [
            'desc' =>
              [
                0 => 'Detached OpenPGP signature',
              ],
            'e' =>
              [
                0 => 'asc',
                1 => 'sig',
                2 => 'pgp',
                3 => 'gpg',
              ],
          ],
        'application/pics-rules' =>
          [
            'e' =>
              [
                0 => 'prf',
              ],
          ],
        'application/pkcs10' =>
          [
            'desc' =>
              [
                0 => 'PKCS#10 certification request',
                1 => 'PKCS: Public-Key Cryptography Standards',
              ],
            'e' =>
              [
                0 => 'p10',
              ],
          ],
        'application/pkcs12' =>
          [
            'a' =>
              [
                0 => 'application/x-pkcs12',
              ],
            'desc' =>
              [
                0 => 'PKCS#12 certificate bundle',
                1 => 'PKCS: Public-Key Cryptography Standards',
              ],
            'e' =>
              [
                0 => 'p12',
                1 => 'pfx',
              ],
          ],
        'application/pkcs7-mime' =>
          [
            'desc' =>
              [
                0 => 'PKCS#7 file',
                1 => 'PKCS: Public-Key Cryptography Standards',
              ],
            'e' =>
              [
                0 => 'p7m',
                1 => 'p7c',
              ],
          ],
        'application/pkcs7-signature' =>
          [
            'desc' =>
              [
                0 => 'Detached S/MIME signature',
                1 => 'S/MIME: Secure/Multipurpose Internet Mail Extensions',
              ],
            'e' =>
              [
                0 => 'p7s',
              ],
          ],
        'application/pkcs8' =>
          [
            'desc' =>
              [
                0 => 'PKCS#8 private key',
                1 => 'PKCS: Public-Key Cryptography Standards',
              ],
            'e' =>
              [
                0 => 'p8',
              ],
          ],
        'application/pkcs8-encrypted' =>
          [
            'desc' =>
              [
                0 => 'PKCS#8 private key (encrypted)',
                1 => 'PKCS: Public-Key Cryptography Standards',
              ],
            'e' =>
              [
                0 => 'p8e',
              ],
          ],
        'application/pkix-attr-cert' =>
          [
            'e' =>
              [
                0 => 'ac',
              ],
          ],
        'application/pkix-cert' =>
          [
            'desc' =>
              [
                0 => 'X.509 certificate',
              ],
            'e' =>
              [
                0 => 'cer',
              ],
          ],
        'application/pkix-crl' =>
          [
            'desc' =>
              [
                0 => 'Certificate revocation list',
              ],
            'e' =>
              [
                0 => 'crl',
              ],
          ],
        'application/pkix-pkipath' =>
          [
            'desc' =>
              [
                0 => 'PkiPath certification path',
              ],
            'e' =>
              [
                0 => 'pkipath',
              ],
          ],
        'application/pkixcmp' =>
          [
            'e' =>
              [
                0 => 'pki',
              ],
          ],
        'application/pls+xml' =>
          [
            'e' =>
              [
                0 => 'pls',
              ],
          ],
        'application/postscript' =>
          [
            'desc' =>
              [
                0 => 'PostScript document',
              ],
            'e' =>
              [
                0 => 'ai',
                1 => 'eps',
                2 => 'ps',
              ],
          ],
        'application/prs.cww' =>
          [
            'e' =>
              [
                0 => 'cww',
              ],
          ],
        'application/pskc+xml' =>
          [
            'e' =>
              [
                0 => 'pskcxml',
              ],
          ],
        'application/ram' =>
          [
            'desc' =>
              [
                0 => 'RealMedia playlist',
              ],
            'e' =>
              [
                0 => 'ram',
              ],
          ],
        'application/raml+yaml' =>
          [
            'desc' =>
              [
                0 => 'RAML document',
                1 => 'RAML: RESTful API Modeling Language',
              ],
            'e' =>
              [
                0 => 'raml',
              ],
          ],
        'application/rdf+xml' =>
          [
            'a' =>
              [
                0 => 'text/rdf',
              ],
            'desc' =>
              [
                0 => 'RDF file',
                1 => 'RDF: Resource Description Framework',
              ],
            'e' =>
              [
                0 => 'rdf',
                1 => 'rdfs',
                2 => 'owl',
              ],
          ],
        'application/reginfo+xml' =>
          [
            'e' =>
              [
                0 => 'rif',
              ],
          ],
        'application/relax-ng-compact-syntax' =>
          [
            'a' =>
              [
                0 => 'application/x-rnc',
              ],
            'desc' =>
              [
                0 => 'RELAX NG XML schema',
                1 => 'RELAX NG: REgular LAnguage for XML Next Generation',
              ],
            'e' =>
              [
                0 => 'rnc',
              ],
          ],
        'application/resource-lists+xml' =>
          [
            'e' =>
              [
                0 => 'rl',
              ],
          ],
        'application/resource-lists-diff+xml' =>
          [
            'e' =>
              [
                0 => 'rld',
              ],
          ],
        'application/rls-services+xml' =>
          [
            'e' =>
              [
                0 => 'rs',
              ],
          ],
        'application/rpki-ghostbusters' =>
          [
            'e' =>
              [
                0 => 'gbr',
              ],
          ],
        'application/rpki-manifest' =>
          [
            'e' =>
              [
                0 => 'mft',
              ],
          ],
        'application/rpki-roa' =>
          [
            'e' =>
              [
                0 => 'roa',
              ],
          ],
        'application/rsd+xml' =>
          [
            'e' =>
              [
                0 => 'rsd',
              ],
          ],
        'application/rss+xml' =>
          [
            'a' =>
              [
                0 => 'text/rss',
              ],
            'desc' =>
              [
                0 => 'RSS summary',
                1 => 'RSS: RDF Site Summary',
              ],
            'e' =>
              [
                0 => 'rss',
              ],
          ],
        'application/rtf' =>
          [
            'a' =>
              [
                0 => 'text/rtf',
              ],
            'desc' =>
              [
                0 => 'RTF document',
                1 => 'RTF: Rich Text Format',
              ],
            'e' =>
              [
                0 => 'rtf',
              ],
          ],
        'application/sbml+xml' =>
          [
            'e' =>
              [
                0 => 'sbml',
              ],
          ],
        'application/schema+json' =>
          [
            'desc' =>
              [
                0 => 'JSON schema',
              ],
            'e' =>
              [
                0 => 'json',
              ],
          ],
        'application/scvp-cv-request' =>
          [
            'e' =>
              [
                0 => 'scq',
              ],
          ],
        'application/scvp-cv-response' =>
          [
            'e' =>
              [
                0 => 'scs',
              ],
          ],
        'application/scvp-vp-request' =>
          [
            'e' =>
              [
                0 => 'spq',
              ],
          ],
        'application/scvp-vp-response' =>
          [
            'e' =>
              [
                0 => 'spp',
              ],
          ],
        'application/sdp' =>
          [
            'a' =>
              [
                0 => 'application/x-sdp',
                1 => 'application/vnd.sdp',
              ],
            'desc' =>
              [
                0 => 'SDP multicast stream file',
                1 => 'SDP: Session Description Protocol',
              ],
            'e' =>
              [
                0 => 'sdp',
              ],
          ],
        'application/set-payment-initiation' =>
          [
            'e' =>
              [
                0 => 'setpay',
              ],
          ],
        'application/set-registration-initiation' =>
          [
            'e' =>
              [
                0 => 'setreg',
              ],
          ],
        'application/shf+xml' =>
          [
            'e' =>
              [
                0 => 'shf',
              ],
          ],
        'application/sieve' =>
          [
            'desc' =>
              [
                0 => 'Sieve mail filter script',
              ],
            'e' =>
              [
                0 => 'siv',
                1 => 'sieve',
              ],
          ],
        'application/smil+xml' =>
          [
            'a' =>
              [
                0 => 'application/smil',
              ],
            'desc' =>
              [
                0 => 'SMIL document',
                1 => 'SMIL: Synchronized Multimedia Integration Language',
              ],
            'e' =>
              [
                0 => 'smi',
                1 => 'smil',
                2 => 'sml',
                3 => 'kino',
              ],
          ],
        'application/sparql-query' =>
          [
            'desc' =>
              [
                0 => 'SPARQL query',
                1 => 'SPARQL: SPARQL Protocol and RDF Query Language',
              ],
            'e' =>
              [
                0 => 'rq',
                1 => 'qs',
              ],
          ],
        'application/sparql-results+xml' =>
          [
            'desc' =>
              [
                0 => 'SPARQL query results',
                1 => 'SPARQL: SPARQL Protocol and RDF Query Language',
              ],
            'e' =>
              [
                0 => 'srx',
              ],
          ],
        'application/sql' =>
          [
            'a' =>
              [
                0 => 'text/x-sql',
              ],
            'desc' =>
              [
                0 => 'SQL code',
              ],
            'e' =>
              [
                0 => 'sql',
              ],
          ],
        'application/srgs' =>
          [
            'e' =>
              [
                0 => 'gram',
              ],
          ],
        'application/srgs+xml' =>
          [
            'e' =>
              [
                0 => 'grxml',
              ],
          ],
        'application/sru+xml' =>
          [
            'e' =>
              [
                0 => 'sru',
              ],
          ],
        'application/ssdl+xml' =>
          [
            'e' =>
              [
                0 => 'ssdl',
              ],
          ],
        'application/ssml+xml' =>
          [
            'e' =>
              [
                0 => 'ssml',
              ],
          ],
        'application/tei+xml' =>
          [
            'e' =>
              [
                0 => 'tei',
                1 => 'teicorpus',
              ],
          ],
        'application/thraud+xml' =>
          [
            'e' =>
              [
                0 => 'tfi',
              ],
          ],
        'application/timestamped-data' =>
          [
            'e' =>
              [
                0 => 'tsd',
              ],
          ],
        'application/toml' =>
          [
            'desc' =>
              [
                0 => 'TOML document',
                1 => 'TOML: Tom\'s Obvious Minimal Language',
              ],
            'e' =>
              [
                0 => 'toml',
              ],
          ],
        'application/trig' =>
          [
            'a' =>
              [
                0 => 'application/x-trig',
              ],
            'desc' =>
              [
                0 => 'TriG RDF document',
                1 => 'TriG: TriG RDF Graph Triple Language',
              ],
            'e' =>
              [
                0 => 'trig',
              ],
          ],
        'application/vnd.3gpp.pic-bw-large' =>
          [
            'e' =>
              [
                0 => 'plb',
              ],
          ],
        'application/vnd.3gpp.pic-bw-small' =>
          [
            'e' =>
              [
                0 => 'psb',
              ],
          ],
        'application/vnd.3gpp.pic-bw-var' =>
          [
            'e' =>
              [
                0 => 'pvb',
              ],
          ],
        'application/vnd.3gpp2.tcap' =>
          [
            'e' =>
              [
                0 => 'tcap',
              ],
          ],
        'application/vnd.3m.post-it-notes' =>
          [
            'e' =>
              [
                0 => 'pwn',
              ],
          ],
        'application/vnd.accpac.simply.aso' =>
          [
            'e' =>
              [
                0 => 'aso',
              ],
          ],
        'application/vnd.accpac.simply.imp' =>
          [
            'e' =>
              [
                0 => 'imp',
              ],
          ],
        'application/vnd.acucobol' =>
          [
            'e' =>
              [
                0 => 'acu',
              ],
          ],
        'application/vnd.acucorp' =>
          [
            'e' =>
              [
                0 => 'atc',
                1 => 'acutc',
              ],
          ],
        'application/vnd.adobe.air-application-installer-package+zip' =>
          [
            'e' =>
              [
                0 => 'air',
              ],
          ],
        'application/vnd.adobe.flash.movie' =>
          [
            'a' =>
              [
                0 => 'application/x-shockwave-flash',
                1 => 'application/futuresplash',
              ],
            'desc' =>
              [
                0 => 'Shockwave Flash file',
              ],
            'e' =>
              [
                0 => 'swf',
                1 => 'spl',
              ],
          ],
        'application/vnd.adobe.formscentral.fcdt' =>
          [
            'e' =>
              [
                0 => 'fcdt',
              ],
          ],
        'application/vnd.adobe.fxp' =>
          [
            'e' =>
              [
                0 => 'fxp',
                1 => 'fxpl',
              ],
          ],
        'application/vnd.adobe.xdp+xml' =>
          [
            'e' =>
              [
                0 => 'xdp',
              ],
          ],
        'application/vnd.adobe.xfdf' =>
          [
            'e' =>
              [
                0 => 'xfdf',
              ],
          ],
        'application/vnd.ahead.space' =>
          [
            'e' =>
              [
                0 => 'ahead',
              ],
          ],
        'application/vnd.airzip.filesecure.azf' =>
          [
            'e' =>
              [
                0 => 'azf',
              ],
          ],
        'application/vnd.airzip.filesecure.azs' =>
          [
            'e' =>
              [
                0 => 'azs',
              ],
          ],
        'application/vnd.amazon.ebook' =>
          [
            'e' =>
              [
                0 => 'azw',
              ],
          ],
        'application/vnd.amazon.mobi8-ebook' =>
          [
            'a' =>
              [
                0 => 'application/x-mobi8-ebook',
              ],
            'desc' =>
              [
                0 => 'Kindle book document',
              ],
            'e' =>
              [
                0 => 'azw3',
                1 => 'kfx',
              ],
          ],
        'application/vnd.americandynamics.acc' =>
          [
            'e' =>
              [
                0 => 'acc',
              ],
          ],
        'application/vnd.amiga.ami' =>
          [
            'e' =>
              [
                0 => 'ami',
              ],
          ],
        'application/vnd.android.package-archive' =>
          [
            'desc' =>
              [
                0 => 'Android package',
              ],
            'e' =>
              [
                0 => 'apk',
              ],
          ],
        'application/vnd.anser-web-certificate-issue-initiation' =>
          [
            'e' =>
              [
                0 => 'cii',
              ],
          ],
        'application/vnd.anser-web-funds-transfer-initiation' =>
          [
            'e' =>
              [
                0 => 'fti',
              ],
          ],
        'application/vnd.antix.game-component' =>
          [
            'e' =>
              [
                0 => 'atx',
              ],
          ],
        'application/vnd.apache.parquet' =>
          [
            'a' =>
              [
                0 => 'application/x-parquet',
              ],
            'desc' =>
              [
                0 => 'Apache Parquet file',
              ],
            'e' =>
              [
                0 => 'parquet',
              ],
          ],
        'application/vnd.appimage' =>
          [
            'desc' =>
              [
                0 => 'AppImage application bundle',
              ],
            'e' =>
              [
                0 => 'appimage',
              ],
          ],
        'application/vnd.apple.installer+xml' =>
          [
            'e' =>
              [
                0 => 'mpkg',
              ],
          ],
        'application/vnd.apple.keynote' =>
          [
            'a' =>
              [
                0 => 'application/x-iwork-keynote-sffkey',
              ],
            'desc' =>
              [
                0 => 'Apple Keynote 5 presentation',
              ],
            'e' =>
              [
                0 => 'key',
              ],
          ],
        'application/vnd.apple.mpegurl' =>
          [
            'desc' =>
              [
                0 => 'Media playlist',
              ],
            'e' =>
              [
                0 => 'm3u8',
                1 => 'm3u',
              ],
          ],
        'application/vnd.apple.numbers' =>
          [
            'a' =>
              [
                0 => 'application/x-iwork-numbers-sffnumbers',
              ],
            'desc' =>
              [
                0 => 'Apple Numbers spreadsheet',
              ],
            'e' =>
              [
                0 => 'numbers',
              ],
          ],
        'application/vnd.apple.pages' =>
          [
            'a' =>
              [
                0 => 'application/x-iwork-pages-sffpages',
              ],
            'desc' =>
              [
                0 => 'Apple Pages document',
              ],
            'e' =>
              [
                0 => 'pages',
              ],
          ],
        'application/vnd.apple.pkpass' =>
          [
            'desc' =>
              [
                0 => 'Apple Wallet pass',
              ],
            'e' =>
              [
                0 => 'pkpass',
              ],
          ],
        'application/vnd.aristanetworks.swi' =>
          [
            'e' =>
              [
                0 => 'swi',
              ],
          ],
        'application/vnd.astraea-software.iota' =>
          [
            'e' =>
              [
                0 => 'iota',
              ],
          ],
        'application/vnd.audiograph' =>
          [
            'e' =>
              [
                0 => 'aep',
              ],
          ],
        'application/vnd.blueice.multipass' =>
          [
            'e' =>
              [
                0 => 'mpm',
              ],
          ],
        'application/vnd.bmi' =>
          [
            'e' =>
              [
                0 => 'bmi',
              ],
          ],
        'application/vnd.businessobjects' =>
          [
            'e' =>
              [
                0 => 'rep',
              ],
          ],
        'application/vnd.chemdraw+xml' =>
          [
            'e' =>
              [
                0 => 'cdxml',
              ],
          ],
        'application/vnd.chess-pgn' =>
          [
            'a' =>
              [
                0 => 'application/x-chess-pgn',
              ],
            'desc' =>
              [
                0 => 'PGN chess game notation',
                1 => 'PGN: Portable Game Notation',
              ],
            'e' =>
              [
                0 => 'pgn',
              ],
          ],
        'application/vnd.chipnuts.karaoke-mmd' =>
          [
            'e' =>
              [
                0 => 'mmd',
              ],
          ],
        'application/vnd.cinderella' =>
          [
            'e' =>
              [
                0 => 'cdy',
              ],
          ],
        'application/vnd.claymore' =>
          [
            'e' =>
              [
                0 => 'cla',
              ],
          ],
        'application/vnd.cloanto.rp9' =>
          [
            'e' =>
              [
                0 => 'rp9',
              ],
          ],
        'application/vnd.clonk.c4group' =>
          [
            'e' =>
              [
                0 => 'c4g',
                1 => 'c4d',
                2 => 'c4f',
                3 => 'c4p',
                4 => 'c4u',
              ],
          ],
        'application/vnd.cluetrust.cartomobile-config' =>
          [
            'e' =>
              [
                0 => 'c11amc',
              ],
          ],
        'application/vnd.cluetrust.cartomobile-config-pkg' =>
          [
            'e' =>
              [
                0 => 'c11amz',
              ],
          ],
        'application/vnd.coffeescript' =>
          [
            'desc' =>
              [
                0 => 'CoffeeScript document',
              ],
            'e' =>
              [
                0 => 'coffee',
              ],
          ],
        'application/vnd.comicbook+zip' =>
          [
            'a' =>
              [
                0 => 'application/x-cbz',
              ],
            'desc' =>
              [
                0 => 'Comic book archive (zip container)',
              ],
            'e' =>
              [
                0 => 'cbz',
              ],
          ],
        'application/vnd.comicbook-rar' =>
          [
            'a' =>
              [
                0 => 'application/x-cbr',
              ],
            'desc' =>
              [
                0 => 'Comic book archive (rar container)',
              ],
            'e' =>
              [
                0 => 'cbr',
                1 => 'cba',
              ],
          ],
        'application/vnd.commonspace' =>
          [
            'e' =>
              [
                0 => 'csp',
              ],
          ],
        'application/vnd.contact.cmsg' =>
          [
            'e' =>
              [
                0 => 'cdbcmsg',
              ],
          ],
        'application/vnd.corel-draw' =>
          [
            'a' =>
              [
                0 => 'application/cdr',
                1 => 'application/coreldraw',
                2 => 'application/x-cdr',
                3 => 'application/x-coreldraw',
                4 => 'image/cdr',
                5 => 'image/x-cdr',
                6 => 'zz-application/zz-winassoc-cdr',
              ],
            'desc' =>
              [
                0 => 'Corel Draw drawing',
              ],
            'e' =>
              [
                0 => 'cdr',
              ],
          ],
        'application/vnd.cosmocaller' =>
          [
            'e' =>
              [
                0 => 'cmc',
              ],
          ],
        'application/vnd.crick.clicker' =>
          [
            'e' =>
              [
                0 => 'clkx',
              ],
          ],
        'application/vnd.crick.clicker.keyboard' =>
          [
            'e' =>
              [
                0 => 'clkk',
              ],
          ],
        'application/vnd.crick.clicker.palette' =>
          [
            'e' =>
              [
                0 => 'clkp',
              ],
          ],
        'application/vnd.crick.clicker.template' =>
          [
            'e' =>
              [
                0 => 'clkt',
              ],
          ],
        'application/vnd.crick.clicker.wordbank' =>
          [
            'e' =>
              [
                0 => 'clkw',
              ],
          ],
        'application/vnd.criticaltools.wbs+xml' =>
          [
            'e' =>
              [
                0 => 'wbs',
              ],
          ],
        'application/vnd.ctc-posml' =>
          [
            'e' =>
              [
                0 => 'pml',
              ],
          ],
        'application/vnd.cups-ppd' =>
          [
            'desc' =>
              [
                0 => 'PostScript printer description',
              ],
            'e' =>
              [
                0 => 'ppd',
              ],
          ],
        'application/vnd.curl.car' =>
          [
            'e' =>
              [
                0 => 'car',
              ],
          ],
        'application/vnd.curl.pcurl' =>
          [
            'e' =>
              [
                0 => 'pcurl',
              ],
          ],
        'application/vnd.dart' =>
          [
            'a' =>
              [
                0 => 'text/x-dart',
              ],
            'desc' =>
              [
                0 => 'Dart source code',
              ],
            'e' =>
              [
                0 => 'dart',
              ],
          ],
        'application/vnd.data-vision.rdz' =>
          [
            'e' =>
              [
                0 => 'rdz',
              ],
          ],
        'application/vnd.dbf' =>
          [
            'a' =>
              [
                0 => 'application/dbase',
                1 => 'application/dbf',
                2 => 'application/x-dbase',
                3 => 'application/x-dbf',
              ],
            'desc' =>
              [
                0 => 'Xbase document',
              ],
            'e' =>
              [
                0 => 'dbf',
              ],
          ],
        'application/vnd.debian.binary-package' =>
          [
            'a' =>
              [
                0 => 'application/x-deb',
                1 => 'application/x-debian-package',
              ],
            'desc' =>
              [
                0 => 'Debian package',
              ],
            'e' =>
              [
                0 => 'deb',
                1 => 'udeb',
              ],
          ],
        'application/vnd.dece.data' =>
          [
            'e' =>
              [
                0 => 'uvf',
                1 => 'uvvf',
                2 => 'uvd',
                3 => 'uvvd',
              ],
          ],
        'application/vnd.dece.ttml+xml' =>
          [
            'e' =>
              [
                0 => 'uvt',
                1 => 'uvvt',
              ],
          ],
        'application/vnd.dece.unspecified' =>
          [
            'e' =>
              [
                0 => 'uvx',
                1 => 'uvvx',
              ],
          ],
        'application/vnd.dece.zip' =>
          [
            'e' =>
              [
                0 => 'uvz',
                1 => 'uvvz',
              ],
          ],
        'application/vnd.denovo.fcselayout-link' =>
          [
            'e' =>
              [
                0 => 'fe_launch',
              ],
          ],
        'application/vnd.dna' =>
          [
            'e' =>
              [
                0 => 'dna',
              ],
          ],
        'application/vnd.dolby.mlp' =>
          [
            'e' =>
              [
                0 => 'mlp',
              ],
          ],
        'application/vnd.dpgraph' =>
          [
            'e' =>
              [
                0 => 'dpg',
              ],
          ],
        'application/vnd.dreamfactory' =>
          [
            'e' =>
              [
                0 => 'dfac',
              ],
          ],
        'application/vnd.ds-keypoint' =>
          [
            'e' =>
              [
                0 => 'kpxx',
              ],
          ],
        'application/vnd.dvb.ait' =>
          [
            'e' =>
              [
                0 => 'ait',
              ],
          ],
        'application/vnd.dvb.service' =>
          [
            'e' =>
              [
                0 => 'svc',
              ],
          ],
        'application/vnd.dynageo' =>
          [
            'e' =>
              [
                0 => 'geo',
              ],
          ],
        'application/vnd.ecowin.chart' =>
          [
            'e' =>
              [
                0 => 'mag',
              ],
          ],
        'application/vnd.efi.img' =>
          [
            'a' =>
              [
                0 => 'application/x-raw-disk-image',
              ],
            'desc' =>
              [
                0 => 'Raw disk image',
              ],
            'e' =>
              [
                0 => 'raw-disk-image',
                1 => 'img',
              ],
          ],
        'application/vnd.efi.iso' =>
          [
            'a' =>
              [
                0 => 'application/x-cd-image',
                1 => 'application/x-iso9660-image',
              ],
            'desc' =>
              [
                0 => 'Raw CD image',
              ],
            'e' =>
              [
                0 => 'iso',
                1 => 'iso9660',
              ],
          ],
        'application/vnd.emusic-emusic_package' =>
          [
            'desc' =>
              [
                0 => 'eMusic download package',
              ],
            'e' =>
              [
                0 => 'emp',
              ],
          ],
        'application/vnd.enliven' =>
          [
            'e' =>
              [
                0 => 'nml',
              ],
          ],
        'application/vnd.epson.esf' =>
          [
            'e' =>
              [
                0 => 'esf',
              ],
          ],
        'application/vnd.epson.msf' =>
          [
            'e' =>
              [
                0 => 'msf',
              ],
          ],
        'application/vnd.epson.quickanime' =>
          [
            'e' =>
              [
                0 => 'qam',
              ],
          ],
        'application/vnd.epson.salt' =>
          [
            'e' =>
              [
                0 => 'slt',
              ],
          ],
        'application/vnd.epson.ssf' =>
          [
            'e' =>
              [
                0 => 'ssf',
              ],
          ],
        'application/vnd.eszigno3+xml' =>
          [
            'e' =>
              [
                0 => 'es3',
                1 => 'et3',
              ],
          ],
        'application/vnd.ezpix-album' =>
          [
            'e' =>
              [
                0 => 'ez2',
              ],
          ],
        'application/vnd.ezpix-package' =>
          [
            'e' =>
              [
                0 => 'ez3',
              ],
          ],
        'application/vnd.fdf' =>
          [
            'e' =>
              [
                0 => 'fdf',
              ],
          ],
        'application/vnd.fdsn.mseed' =>
          [
            'e' =>
              [
                0 => 'mseed',
              ],
          ],
        'application/vnd.fdsn.seed' =>
          [
            'e' =>
              [
                0 => 'seed',
                1 => 'dataless',
              ],
          ],
        'application/vnd.flatpak' =>
          [
            'a' =>
              [
                0 => 'application/vnd.xdgapp',
              ],
            'desc' =>
              [
                0 => 'Flatpak application bundle',
              ],
            'e' =>
              [
                0 => 'flatpak',
                1 => 'xdgapp',
              ],
          ],
        'application/vnd.flatpak.ref' =>
          [
            'desc' =>
              [
                0 => 'Flatpak repository reference',
              ],
            'e' =>
              [
                0 => 'flatpakref',
              ],
          ],
        'application/vnd.flatpak.repo' =>
          [
            'desc' =>
              [
                0 => 'Flatpak repository description',
              ],
            'e' =>
              [
                0 => 'flatpakrepo',
              ],
          ],
        'application/vnd.flographit' =>
          [
            'e' =>
              [
                0 => 'gph',
              ],
          ],
        'application/vnd.fluxtime.clip' =>
          [
            'e' =>
              [
                0 => 'ftc',
              ],
          ],
        'application/vnd.framemaker' =>
          [
            'a' =>
              [
                0 => 'application/x-frame',
              ],
            'desc' =>
              [
                0 => 'Adobe FrameMaker document',
              ],
            'e' =>
              [
                0 => 'fm',
                1 => 'frame',
                2 => 'maker',
                3 => 'book',
              ],
          ],
        'application/vnd.frogans.fnc' =>
          [
            'e' =>
              [
                0 => 'fnc',
              ],
          ],
        'application/vnd.frogans.ltf' =>
          [
            'e' =>
              [
                0 => 'ltf',
              ],
          ],
        'application/vnd.fsc.weblaunch' =>
          [
            'e' =>
              [
                0 => 'fsc',
              ],
          ],
        'application/vnd.fujitsu.oasys' =>
          [
            'e' =>
              [
                0 => 'oas',
              ],
          ],
        'application/vnd.fujitsu.oasys2' =>
          [
            'e' =>
              [
                0 => 'oa2',
              ],
          ],
        'application/vnd.fujitsu.oasys3' =>
          [
            'e' =>
              [
                0 => 'oa3',
              ],
          ],
        'application/vnd.fujitsu.oasysgp' =>
          [
            'e' =>
              [
                0 => 'fg5',
              ],
          ],
        'application/vnd.fujitsu.oasysprs' =>
          [
            'e' =>
              [
                0 => 'bh2',
              ],
          ],
        'application/vnd.fujixerox.ddd' =>
          [
            'e' =>
              [
                0 => 'ddd',
              ],
          ],
        'application/vnd.fujixerox.docuworks' =>
          [
            'e' =>
              [
                0 => 'xdw',
              ],
          ],
        'application/vnd.fujixerox.docuworks.binder' =>
          [
            'e' =>
              [
                0 => 'xbd',
              ],
          ],
        'application/vnd.fuzzysheet' =>
          [
            'e' =>
              [
                0 => 'fzs',
              ],
          ],
        'application/vnd.genomatix.tuxedo' =>
          [
            'e' =>
              [
                0 => 'txd',
              ],
          ],
        'application/vnd.geogebra.file' =>
          [
            'e' =>
              [
                0 => 'ggb',
              ],
          ],
        'application/vnd.geogebra.slides' =>
          [
            'e' =>
              [
                0 => 'ggs',
              ],
          ],
        'application/vnd.geogebra.tool' =>
          [
            'e' =>
              [
                0 => 'ggt',
              ],
          ],
        'application/vnd.geometry-explorer' =>
          [
            'e' =>
              [
                0 => 'gex',
                1 => 'gre',
              ],
          ],
        'application/vnd.geonext' =>
          [
            'e' =>
              [
                0 => 'gxt',
              ],
          ],
        'application/vnd.geoplan' =>
          [
            'e' =>
              [
                0 => 'g2w',
              ],
          ],
        'application/vnd.geospace' =>
          [
            'e' =>
              [
                0 => 'g3w',
              ],
          ],
        'application/vnd.gerber' =>
          [
            'a' =>
              [
                0 => 'application/x-gerber',
              ],
            'desc' =>
              [
                0 => 'Gerber file',
              ],
            'e' =>
              [
                0 => 'gbr',
              ],
          ],
        'application/vnd.gmx' =>
          [
            'e' =>
              [
                0 => 'gmx',
              ],
          ],
        'application/vnd.google-earth.kml+xml' =>
          [
            'desc' =>
              [
                0 => 'KML geographic data',
                1 => 'KML: Keyhole Markup Language',
              ],
            'e' =>
              [
                0 => 'kml',
              ],
          ],
        'application/vnd.google-earth.kmz' =>
          [
            'desc' =>
              [
                0 => 'KML geographic compressed data',
                1 => 'KML: Keyhole Markup Language',
              ],
            'e' =>
              [
                0 => 'kmz',
              ],
          ],
        'application/vnd.grafeq' =>
          [
            'e' =>
              [
                0 => 'gqf',
                1 => 'gqs',
              ],
          ],
        'application/vnd.groove-account' =>
          [
            'e' =>
              [
                0 => 'gac',
              ],
          ],
        'application/vnd.groove-help' =>
          [
            'e' =>
              [
                0 => 'ghf',
              ],
          ],
        'application/vnd.groove-identity-message' =>
          [
            'e' =>
              [
                0 => 'gim',
              ],
          ],
        'application/vnd.groove-injector' =>
          [
            'e' =>
              [
                0 => 'grv',
              ],
          ],
        'application/vnd.groove-tool-message' =>
          [
            'e' =>
              [
                0 => 'gtm',
              ],
          ],
        'application/vnd.groove-tool-template' =>
          [
            'e' =>
              [
                0 => 'tpl',
              ],
          ],
        'application/vnd.groove-vcard' =>
          [
            'e' =>
              [
                0 => 'vcg',
              ],
          ],
        'application/vnd.hal+xml' =>
          [
            'e' =>
              [
                0 => 'hal',
              ],
          ],
        'application/vnd.handheld-entertainment+xml' =>
          [
            'e' =>
              [
                0 => 'zmm',
              ],
          ],
        'application/vnd.hbci' =>
          [
            'e' =>
              [
                0 => 'hbci',
              ],
          ],
        'application/vnd.hhe.lesson-player' =>
          [
            'e' =>
              [
                0 => 'les',
              ],
          ],
        'application/vnd.hp-hpgl' =>
          [
            'desc' =>
              [
                0 => 'HPGL file',
                1 => 'HPGL: HP Graphics Language',
              ],
            'e' =>
              [
                0 => 'hpgl',
              ],
          ],
        'application/vnd.hp-hpid' =>
          [
            'e' =>
              [
                0 => 'hpid',
              ],
          ],
        'application/vnd.hp-hps' =>
          [
            'e' =>
              [
                0 => 'hps',
              ],
          ],
        'application/vnd.hp-jlyt' =>
          [
            'e' =>
              [
                0 => 'jlt',
              ],
          ],
        'application/vnd.hp-pcl' =>
          [
            'desc' =>
              [
                0 => 'PCL file',
                1 => 'PCL: HP Printer Control Language',
              ],
            'e' =>
              [
                0 => 'pcl',
              ],
          ],
        'application/vnd.hp-pclxl' =>
          [
            'e' =>
              [
                0 => 'pclxl',
              ],
          ],
        'application/vnd.hydrostatix.sof-data' =>
          [
            'e' =>
              [
                0 => 'sfd-hdstx',
              ],
          ],
        'application/vnd.ibm.minipay' =>
          [
            'e' =>
              [
                0 => 'mpy',
              ],
          ],
        'application/vnd.ibm.modcap' =>
          [
            'e' =>
              [
                0 => 'afp',
                1 => 'listafp',
                2 => 'list3820',
              ],
          ],
        'application/vnd.ibm.rights-management' =>
          [
            'e' =>
              [
                0 => 'irm',
              ],
          ],
        'application/vnd.ibm.secure-container' =>
          [
            'e' =>
              [
                0 => 'sc',
              ],
          ],
        'application/vnd.iccprofile' =>
          [
            'desc' =>
              [
                0 => 'ICC profile',
                1 => 'ICC: International Color Consortium',
              ],
            'e' =>
              [
                0 => 'icc',
                1 => 'icm',
              ],
          ],
        'application/vnd.igloader' =>
          [
            'e' =>
              [
                0 => 'igl',
              ],
          ],
        'application/vnd.immervision-ivp' =>
          [
            'e' =>
              [
                0 => 'ivp',
              ],
          ],
        'application/vnd.immervision-ivu' =>
          [
            'e' =>
              [
                0 => 'ivu',
              ],
          ],
        'application/vnd.insors.igm' =>
          [
            'e' =>
              [
                0 => 'igm',
              ],
          ],
        'application/vnd.intercon.formnet' =>
          [
            'e' =>
              [
                0 => 'xpw',
                1 => 'xpx',
              ],
          ],
        'application/vnd.intergeo' =>
          [
            'e' =>
              [
                0 => 'i2g',
              ],
          ],
        'application/vnd.intu.qbo' =>
          [
            'e' =>
              [
                0 => 'qbo',
              ],
          ],
        'application/vnd.intu.qfx' =>
          [
            'e' =>
              [
                0 => 'qfx',
              ],
          ],
        'application/vnd.ipunplugged.rcprofile' =>
          [
            'e' =>
              [
                0 => 'rcprofile',
              ],
          ],
        'application/vnd.irepository.package+xml' =>
          [
            'e' =>
              [
                0 => 'irp',
              ],
          ],
        'application/vnd.is-xpr' =>
          [
            'e' =>
              [
                0 => 'xpr',
              ],
          ],
        'application/vnd.isac.fcs' =>
          [
            'e' =>
              [
                0 => 'fcs',
              ],
          ],
        'application/vnd.jam' =>
          [
            'e' =>
              [
                0 => 'jam',
              ],
          ],
        'application/vnd.jcp.javame.midlet-rms' =>
          [
            'e' =>
              [
                0 => 'rms',
              ],
          ],
        'application/vnd.jisp' =>
          [
            'e' =>
              [
                0 => 'jisp',
              ],
          ],
        'application/vnd.joost.joda-archive' =>
          [
            'e' =>
              [
                0 => 'joda',
              ],
          ],
        'application/vnd.kahootz' =>
          [
            'e' =>
              [
                0 => 'ktz',
                1 => 'ktr',
              ],
          ],
        'application/vnd.kde.karbon' =>
          [
            'e' =>
              [
                0 => 'karbon',
              ],
          ],
        'application/vnd.kde.kchart' =>
          [
            'e' =>
              [
                0 => 'chrt',
              ],
          ],
        'application/vnd.kde.kformula' =>
          [
            'e' =>
              [
                0 => 'kfo',
              ],
          ],
        'application/vnd.kde.kivio' =>
          [
            'e' =>
              [
                0 => 'flw',
              ],
          ],
        'application/vnd.kde.kontour' =>
          [
            'e' =>
              [
                0 => 'kon',
              ],
          ],
        'application/vnd.kde.kpresenter' =>
          [
            'e' =>
              [
                0 => 'kpr',
                1 => 'kpt',
              ],
          ],
        'application/vnd.kde.kspread' =>
          [
            'e' =>
              [
                0 => 'ksp',
              ],
          ],
        'application/vnd.kde.kword' =>
          [
            'e' =>
              [
                0 => 'kwd',
                1 => 'kwt',
              ],
          ],
        'application/vnd.kenameaapp' =>
          [
            'e' =>
              [
                0 => 'htke',
              ],
          ],
        'application/vnd.kidspiration' =>
          [
            'e' =>
              [
                0 => 'kia',
              ],
          ],
        'application/vnd.kinar' =>
          [
            'e' =>
              [
                0 => 'kne',
                1 => 'knp',
              ],
          ],
        'application/vnd.koan' =>
          [
            'e' =>
              [
                0 => 'skp',
                1 => 'skd',
                2 => 'skt',
                3 => 'skm',
              ],
          ],
        'application/vnd.kodak-descriptor' =>
          [
            'e' =>
              [
                0 => 'sse',
              ],
          ],
        'application/vnd.las.las+xml' =>
          [
            'e' =>
              [
                0 => 'lasxml',
              ],
          ],
        'application/vnd.llamagraphics.life-balance.desktop' =>
          [
            'e' =>
              [
                0 => 'lbd',
              ],
          ],
        'application/vnd.llamagraphics.life-balance.exchange+xml' =>
          [
            'e' =>
              [
                0 => 'lbe',
              ],
          ],
        'application/vnd.lotus-1-2-3' =>
          [
            'a' =>
              [
                0 => 'application/x-lotus123',
                1 => 'application/x-123',
                2 => 'application/lotus123',
                3 => 'application/wk1',
                4 => 'zz-application/zz-winassoc-123',
              ],
            'desc' =>
              [
                0 => 'Lotus 1-2-3 spreadsheet',
              ],
            'e' =>
              [
                0 => '123',
                1 => 'wk1',
                2 => 'wk3',
                3 => 'wk4',
                4 => 'wks',
              ],
          ],
        'application/vnd.lotus-approach' =>
          [
            'e' =>
              [
                0 => 'apr',
              ],
          ],
        'application/vnd.lotus-freelance' =>
          [
            'e' =>
              [
                0 => 'pre',
              ],
          ],
        'application/vnd.lotus-notes' =>
          [
            'e' =>
              [
                0 => 'nsf',
              ],
          ],
        'application/vnd.lotus-organizer' =>
          [
            'e' =>
              [
                0 => 'org',
              ],
          ],
        'application/vnd.lotus-screencam' =>
          [
            'e' =>
              [
                0 => 'scm',
              ],
          ],
        'application/vnd.lotus-wordpro' =>
          [
            'desc' =>
              [
                0 => 'Lotus Word Pro document',
              ],
            'e' =>
              [
                0 => 'lwp',
              ],
          ],
        'application/vnd.macports.portpkg' =>
          [
            'e' =>
              [
                0 => 'portpkg',
              ],
          ],
        'application/vnd.mcd' =>
          [
            'e' =>
              [
                0 => 'mcd',
              ],
          ],
        'application/vnd.medcalcdata' =>
          [
            'e' =>
              [
                0 => 'mc1',
              ],
          ],
        'application/vnd.mediastation.cdkey' =>
          [
            'e' =>
              [
                0 => 'cdkey',
              ],
          ],
        'application/vnd.mfer' =>
          [
            'e' =>
              [
                0 => 'mwf',
              ],
          ],
        'application/vnd.mfmp' =>
          [
            'e' =>
              [
                0 => 'mfm',
              ],
          ],
        'application/vnd.micrografx.flo' =>
          [
            'e' =>
              [
                0 => 'flo',
              ],
          ],
        'application/vnd.micrografx.igx' =>
          [
            'e' =>
              [
                0 => 'igx',
              ],
          ],
        'application/vnd.microsoft.portable-executable' =>
          [
            'desc' =>
              [
                0 => 'Windows or EFI program',
                1 => 'EFI: Extensible Firmware Interface',
              ],
            'e' =>
              [
                0 => 'exe',
                1 => 'dll',
                2 => 'cpl',
                3 => 'drv',
                4 => 'scr',
                5 => 'efi',
                6 => 'ocx',
                7 => 'sys',
              ],
          ],
        'application/vnd.mif' =>
          [
            'e' =>
              [
                0 => 'mif',
              ],
          ],
        'application/vnd.mobius.daf' =>
          [
            'e' =>
              [
                0 => 'daf',
              ],
          ],
        'application/vnd.mobius.dis' =>
          [
            'e' =>
              [
                0 => 'dis',
              ],
          ],
        'application/vnd.mobius.mbk' =>
          [
            'e' =>
              [
                0 => 'mbk',
              ],
          ],
        'application/vnd.mobius.mqy' =>
          [
            'e' =>
              [
                0 => 'mqy',
              ],
          ],
        'application/vnd.mobius.msl' =>
          [
            'e' =>
              [
                0 => 'msl',
              ],
          ],
        'application/vnd.mobius.plc' =>
          [
            'e' =>
              [
                0 => 'plc',
              ],
          ],
        'application/vnd.mobius.txf' =>
          [
            'e' =>
              [
                0 => 'txf',
              ],
          ],
        'application/vnd.mophun.application' =>
          [
            'e' =>
              [
                0 => 'mpn',
              ],
          ],
        'application/vnd.mophun.certificate' =>
          [
            'e' =>
              [
                0 => 'mpc',
              ],
          ],
        'application/vnd.mozilla.xul+xml' =>
          [
            'desc' =>
              [
                0 => 'XUL interface document',
                1 => 'XUL: XML User interface markup Language',
              ],
            'e' =>
              [
                0 => 'xul',
              ],
          ],
        'application/vnd.ms-access' =>
          [
            'a' =>
              [
                0 => 'application/msaccess',
                1 => 'application/vnd.msaccess',
                2 => 'application/x-msaccess',
                3 => 'application/mdb',
                4 => 'application/x-mdb',
                5 => 'zz-application/zz-winassoc-mdb',
              ],
            'desc' =>
              [
                0 => 'JET database',
                1 => 'JET: Joint Engine Technology',
              ],
            'e' =>
              [
                0 => 'mdb',
              ],
          ],
        'application/vnd.ms-artgalry' =>
          [
            'e' =>
              [
                0 => 'cil',
              ],
          ],
        'application/vnd.ms-asf' =>
          [
            'a' =>
              [
                0 => 'video/x-ms-wm',
                1 => 'video/x-ms-asf',
                2 => 'video/x-ms-asf-plugin',
              ],
            'desc' =>
              [
                0 => 'ASF video',
                1 => 'ASF: Advanced Streaming Format',
              ],
            'e' =>
              [
                0 => 'asf',
                1 => 'wm',
              ],
          ],
        'application/vnd.ms-cab-compressed' =>
          [
            'a' =>
              [
                0 => 'zz-application/zz-winassoc-cab',
              ],
            'desc' =>
              [
                0 => 'Microsoft Cabinet archive',
              ],
            'e' =>
              [
                0 => 'cab',
              ],
          ],
        'application/vnd.ms-excel' =>
          [
            'a' =>
              [
                0 => 'application/msexcel',
                1 => 'application/x-msexcel',
                2 => 'zz-application/zz-winassoc-xls',
              ],
            'desc' =>
              [
                0 => 'Excel spreadsheet',
              ],
            'e' =>
              [
                0 => 'xls',
                1 => 'xlm',
                2 => 'xla',
                3 => 'xlc',
                4 => 'xlt',
                5 => 'xlw',
                6 => 'xll',
                7 => 'xld',
              ],
          ],
        'application/vnd.ms-excel.addin.macroenabled.12' =>
          [
            'desc' =>
              [
                0 => 'Excel add-in',
              ],
            'e' =>
              [
                0 => 'xlam',
              ],
          ],
        'application/vnd.ms-excel.sheet.binary.macroenabled.12' =>
          [
            'desc' =>
              [
                0 => 'Excel 2007 binary spreadsheet',
              ],
            'e' =>
              [
                0 => 'xlsb',
              ],
          ],
        'application/vnd.ms-excel.sheet.macroenabled.12' =>
          [
            'desc' =>
              [
                0 => 'Excel spreadsheet',
              ],
            'e' =>
              [
                0 => 'xlsm',
              ],
          ],
        'application/vnd.ms-excel.template.macroenabled.12' =>
          [
            'desc' =>
              [
                0 => 'Excel spreadsheet template',
              ],
            'e' =>
              [
                0 => 'xltm',
              ],
          ],
        'application/vnd.ms-fontobject' =>
          [
            'e' =>
              [
                0 => 'eot',
              ],
          ],
        'application/vnd.ms-htmlhelp' =>
          [
            'a' =>
              [
                0 => 'application/x-chm',
              ],
            'desc' =>
              [
                0 => 'CHM document',
                1 => 'CHM: Compiled Help Modules',
              ],
            'e' =>
              [
                0 => 'chm',
              ],
          ],
        'application/vnd.ms-ims' =>
          [
            'e' =>
              [
                0 => 'ims',
              ],
          ],
        'application/vnd.ms-lrm' =>
          [
            'e' =>
              [
                0 => 'lrm',
              ],
          ],
        'application/vnd.ms-officetheme' =>
          [
            'desc' =>
              [
                0 => 'Microsoft Office 2007 theme',
              ],
            'e' =>
              [
                0 => 'thmx',
              ],
          ],
        'application/vnd.ms-pki.seccat' =>
          [
            'e' =>
              [
                0 => 'cat',
              ],
          ],
        'application/vnd.ms-pki.stl' =>
          [
            'e' =>
              [
                0 => 'stl',
              ],
          ],
        'application/vnd.ms-powerpoint' =>
          [
            'a' =>
              [
                0 => 'application/powerpoint',
                1 => 'application/mspowerpoint',
                2 => 'application/x-mspowerpoint',
              ],
            'desc' =>
              [
                0 => 'PowerPoint presentation',
              ],
            'e' =>
              [
                0 => 'ppt',
                1 => 'pps',
                2 => 'pot',
                3 => 'ppz',
              ],
          ],
        'application/vnd.ms-powerpoint.addin.macroenabled.12' =>
          [
            'desc' =>
              [
                0 => 'PowerPoint add-in',
              ],
            'e' =>
              [
                0 => 'ppam',
              ],
          ],
        'application/vnd.ms-powerpoint.presentation.macroenabled.12' =>
          [
            'desc' =>
              [
                0 => 'PowerPoint presentation',
              ],
            'e' =>
              [
                0 => 'pptm',
              ],
          ],
        'application/vnd.ms-powerpoint.slide.macroenabled.12' =>
          [
            'desc' =>
              [
                0 => 'PowerPoint slide',
              ],
            'e' =>
              [
                0 => 'sldm',
              ],
          ],
        'application/vnd.ms-powerpoint.slideshow.macroenabled.12' =>
          [
            'desc' =>
              [
                0 => 'PowerPoint presentation',
              ],
            'e' =>
              [
                0 => 'ppsm',
              ],
          ],
        'application/vnd.ms-powerpoint.template.macroenabled.12' =>
          [
            'desc' =>
              [
                0 => 'PowerPoint presentation template',
              ],
            'e' =>
              [
                0 => 'potm',
              ],
          ],
        'application/vnd.ms-project' =>
          [
            'e' =>
              [
                0 => 'mpp',
                1 => 'mpt',
              ],
          ],
        'application/vnd.ms-publisher' =>
          [
            'desc' =>
              [
                0 => 'Microsoft Publisher document',
              ],
            'e' =>
              [
                0 => 'pub',
              ],
          ],
        'application/vnd.ms-tnef' =>
          [
            'a' =>
              [
                0 => 'application/ms-tnef',
              ],
            'desc' =>
              [
                0 => 'TNEF message',
                1 => 'TNEF: Transport Neutral Encapsulation Format',
              ],
            'e' =>
              [
                0 => 'tnef',
                1 => 'tnf',
              ],
          ],
        'application/vnd.ms-visio.drawing.macroenabled.main+xml' =>
          [
            'desc' =>
              [
                0 => 'Office Open XML Visio drawing',
              ],
            'e' =>
              [
                0 => 'vsdm',
              ],
          ],
        'application/vnd.ms-visio.drawing.main+xml' =>
          [
            'desc' =>
              [
                0 => 'Office Open XML Visio drawing',
              ],
            'e' =>
              [
                0 => 'vsdx',
              ],
          ],
        'application/vnd.ms-visio.stencil.macroenabled.main+xml' =>
          [
            'desc' =>
              [
                0 => 'Office Open XML Visio stencil',
              ],
            'e' =>
              [
                0 => 'vssm',
              ],
          ],
        'application/vnd.ms-visio.stencil.main+xml' =>
          [
            'desc' =>
              [
                0 => 'Office Open XML Visio stencil',
              ],
            'e' =>
              [
                0 => 'vssx',
              ],
          ],
        'application/vnd.ms-visio.template.macroenabled.main+xml' =>
          [
            'desc' =>
              [
                0 => 'Office Open XML Visio template',
              ],
            'e' =>
              [
                0 => 'vstm',
              ],
          ],
        'application/vnd.ms-visio.template.main+xml' =>
          [
            'desc' =>
              [
                0 => 'Office Open XML Visio template',
              ],
            'e' =>
              [
                0 => 'vstx',
              ],
          ],
        'application/vnd.ms-word.document.macroenabled.12' =>
          [
            'desc' =>
              [
                0 => 'Word document',
              ],
            'e' =>
              [
                0 => 'docm',
              ],
          ],
        'application/vnd.ms-word.template.macroenabled.12' =>
          [
            'desc' =>
              [
                0 => 'Word document template',
              ],
            'e' =>
              [
                0 => 'dotm',
              ],
          ],
        'application/vnd.ms-works' =>
          [
            'desc' =>
              [
                0 => 'Microsoft Works document',
              ],
            'e' =>
              [
                0 => 'wps',
                1 => 'wks',
                2 => 'wcm',
                3 => 'wdb',
                4 => 'xlr',
              ],
          ],
        'application/vnd.ms-wpl' =>
          [
            'desc' =>
              [
                0 => 'WPL playlist',
                1 => 'WPL: Windows Media Player Playlist',
              ],
            'e' =>
              [
                0 => 'wpl',
              ],
          ],
        'application/vnd.ms-xpsdocument' =>
          [
            'a' =>
              [
                0 => 'application/xps',
              ],
            'desc' =>
              [
                0 => 'XPS document',
                1 => 'XPS: XML Paper Specification',
              ],
            'e' =>
              [
                0 => 'xps',
              ],
          ],
        'application/vnd.mseq' =>
          [
            'e' =>
              [
                0 => 'mseq',
              ],
          ],
        'application/vnd.musician' =>
          [
            'e' =>
              [
                0 => 'mus',
              ],
          ],
        'application/vnd.muvee.style' =>
          [
            'e' =>
              [
                0 => 'msty',
              ],
          ],
        'application/vnd.mynfc' =>
          [
            'e' =>
              [
                0 => 'taglet',
              ],
          ],
        'application/vnd.neurolanguage.nlu' =>
          [
            'e' =>
              [
                0 => 'nlu',
              ],
          ],
        'application/vnd.nintendo.snes.rom' =>
          [
            'a' =>
              [
                0 => 'application/x-snes-rom',
              ],
            'desc' =>
              [
                0 => 'Super NES ROM',
              ],
            'e' =>
              [
                0 => 'sfc',
                1 => 'smc',
              ],
          ],
        'application/vnd.nitf' =>
          [
            'e' =>
              [
                0 => 'ntf',
                1 => 'nitf',
              ],
          ],
        'application/vnd.noblenet-directory' =>
          [
            'e' =>
              [
                0 => 'nnd',
              ],
          ],
        'application/vnd.noblenet-sealer' =>
          [
            'e' =>
              [
                0 => 'nns',
              ],
          ],
        'application/vnd.noblenet-web' =>
          [
            'e' =>
              [
                0 => 'nnw',
              ],
          ],
        'application/vnd.nokia.n-gage.data' =>
          [
            'e' =>
              [
                0 => 'ngdat',
              ],
          ],
        'application/vnd.nokia.n-gage.symbian.install' =>
          [
            'e' =>
              [
                0 => 'n-gage',
              ],
          ],
        'application/vnd.nokia.radio-preset' =>
          [
            'e' =>
              [
                0 => 'rpst',
              ],
          ],
        'application/vnd.nokia.radio-presets' =>
          [
            'e' =>
              [
                0 => 'rpss',
              ],
          ],
        'application/vnd.novadigm.edm' =>
          [
            'e' =>
              [
                0 => 'edm',
              ],
          ],
        'application/vnd.novadigm.edx' =>
          [
            'e' =>
              [
                0 => 'edx',
              ],
          ],
        'application/vnd.novadigm.ext' =>
          [
            'e' =>
              [
                0 => 'ext',
              ],
          ],
        'application/vnd.oasis.opendocument.base' =>
          [
            'a' =>
              [
                0 => 'application/vnd.oasis.opendocument.database',
                1 => 'application/vnd.sun.xml.base',
              ],
            'desc' =>
              [
                0 => 'ODB database',
                1 => 'ODB: OpenDocument Database',
              ],
            'e' =>
              [
                0 => 'odb',
              ],
          ],
        'application/vnd.oasis.opendocument.chart' =>
          [
            'desc' =>
              [
                0 => 'ODC chart',
                1 => 'ODC: OpenDocument Chart',
              ],
            'e' =>
              [
                0 => 'odc',
              ],
          ],
        'application/vnd.oasis.opendocument.chart-template' =>
          [
            'desc' =>
              [
                0 => 'ODC template',
                1 => 'ODC: OpenDocument Chart',
              ],
            'e' =>
              [
                0 => 'otc',
              ],
          ],
        'application/vnd.oasis.opendocument.formula' =>
          [
            'desc' =>
              [
                0 => 'ODF formula',
                1 => 'ODF: OpenDocument Formula',
              ],
            'e' =>
              [
                0 => 'odf',
              ],
          ],
        'application/vnd.oasis.opendocument.formula-template' =>
          [
            'desc' =>
              [
                0 => 'ODF template',
                1 => 'ODF: OpenDocument Formula',
              ],
            'e' =>
              [
                0 => 'odft',
                1 => 'otf',
              ],
          ],
        'application/vnd.oasis.opendocument.graphics' =>
          [
            'desc' =>
              [
                0 => 'ODG drawing',
                1 => 'ODG: OpenDocument Drawing',
              ],
            'e' =>
              [
                0 => 'odg',
              ],
          ],
        'application/vnd.oasis.opendocument.graphics-flat-xml' =>
          [
            'desc' =>
              [
                0 => 'ODG drawing (Flat XML)',
                1 => 'FODG: OpenDocument Drawing (Flat XML)',
              ],
            'e' =>
              [
                0 => 'fodg',
              ],
          ],
        'application/vnd.oasis.opendocument.graphics-template' =>
          [
            'desc' =>
              [
                0 => 'ODG template',
                1 => 'ODG: OpenDocument Drawing',
              ],
            'e' =>
              [
                0 => 'otg',
              ],
          ],
        'application/vnd.oasis.opendocument.image' =>
          [
            'desc' =>
              [
                0 => 'ODI image',
                1 => 'ODI: OpenDocument Image',
              ],
            'e' =>
              [
                0 => 'odi',
              ],
          ],
        'application/vnd.oasis.opendocument.image-template' =>
          [
            'e' =>
              [
                0 => 'oti',
              ],
          ],
        'application/vnd.oasis.opendocument.presentation' =>
          [
            'desc' =>
              [
                0 => 'ODP presentation',
                1 => 'ODP: OpenDocument Presentation',
              ],
            'e' =>
              [
                0 => 'odp',
              ],
          ],
        'application/vnd.oasis.opendocument.presentation-flat-xml' =>
          [
            'desc' =>
              [
                0 => 'ODP presentation (Flat XML)',
                1 => 'FODP: OpenDocument Presentation (Flat XML)',
              ],
            'e' =>
              [
                0 => 'fodp',
              ],
          ],
        'application/vnd.oasis.opendocument.presentation-template' =>
          [
            'desc' =>
              [
                0 => 'ODP template',
                1 => 'ODP: OpenDocument Presentation',
              ],
            'e' =>
              [
                0 => 'otp',
              ],
          ],
        'application/vnd.oasis.opendocument.spreadsheet' =>
          [
            'desc' =>
              [
                0 => 'ODS spreadsheet',
                1 => 'ODS: OpenDocument Spreadsheet',
              ],
            'e' =>
              [
                0 => 'ods',
              ],
          ],
        'application/vnd.oasis.opendocument.spreadsheet-flat-xml' =>
          [
            'desc' =>
              [
                0 => 'ODS spreadsheet (Flat XML)',
                1 => 'FODS: OpenDocument Spreadsheet (Flat XML)',
              ],
            'e' =>
              [
                0 => 'fods',
              ],
          ],
        'application/vnd.oasis.opendocument.spreadsheet-template' =>
          [
            'desc' =>
              [
                0 => 'ODS template',
                1 => 'ODS: OpenDocument Spreadsheet',
              ],
            'e' =>
              [
                0 => 'ots',
              ],
          ],
        'application/vnd.oasis.opendocument.text' =>
          [
            'desc' =>
              [
                0 => 'ODT document',
                1 => 'ODT: OpenDocument Text',
              ],
            'e' =>
              [
                0 => 'odt',
              ],
          ],
        'application/vnd.oasis.opendocument.text-flat-xml' =>
          [
            'desc' =>
              [
                0 => 'ODT document (Flat XML)',
                1 => 'FODT: OpenDocument Text (Flat XML)',
              ],
            'e' =>
              [
                0 => 'fodt',
              ],
          ],
        'application/vnd.oasis.opendocument.text-master' =>
          [
            'desc' =>
              [
                0 => 'ODM document',
                1 => 'ODM: OpenDocument Master',
              ],
            'e' =>
              [
                0 => 'odm',
              ],
          ],
        'application/vnd.oasis.opendocument.text-master-template' =>
          [
            'desc' =>
              [
                0 => 'ODM template',
                1 => 'ODM: OpenDocument Master',
              ],
            'e' =>
              [
                0 => 'otm',
              ],
          ],
        'application/vnd.oasis.opendocument.text-template' =>
          [
            'desc' =>
              [
                0 => 'ODT template',
                1 => 'ODT: OpenDocument Text',
              ],
            'e' =>
              [
                0 => 'ott',
              ],
          ],
        'application/vnd.oasis.opendocument.text-web' =>
          [
            'desc' =>
              [
                0 => 'OTH template',
                1 => 'OTH: OpenDocument HTML',
              ],
            'e' =>
              [
                0 => 'oth',
              ],
          ],
        'application/vnd.olpc-sugar' =>
          [
            'e' =>
              [
                0 => 'xo',
              ],
          ],
        'application/vnd.oma.dd2+xml' =>
          [
            'e' =>
              [
                0 => 'dd2',
              ],
          ],
        'application/vnd.openofficeorg.extension' =>
          [
            'desc' =>
              [
                0 => 'LibreOffice extension',
              ],
            'e' =>
              [
                0 => 'oxt',
              ],
          ],
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' =>
          [
            'desc' =>
              [
                0 => 'PowerPoint 2007 presentation',
              ],
            'e' =>
              [
                0 => 'pptx',
              ],
          ],
        'application/vnd.openxmlformats-officedocument.presentationml.slide' =>
          [
            'desc' =>
              [
                0 => 'PowerPoint 2007 slide',
              ],
            'e' =>
              [
                0 => 'sldx',
              ],
          ],
        'application/vnd.openxmlformats-officedocument.presentationml.slideshow' =>
          [
            'desc' =>
              [
                0 => 'PowerPoint 2007 show',
              ],
            'e' =>
              [
                0 => 'ppsx',
              ],
          ],
        'application/vnd.openxmlformats-officedocument.presentationml.template' =>
          [
            'desc' =>
              [
                0 => 'PowerPoint 2007 presentation template',
              ],
            'e' =>
              [
                0 => 'potx',
              ],
          ],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' =>
          [
            'desc' =>
              [
                0 => 'Excel 2007 spreadsheet',
              ],
            'e' =>
              [
                0 => 'xlsx',
              ],
          ],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.template' =>
          [
            'desc' =>
              [
                0 => 'Excel 2007 spreadsheet template',
              ],
            'e' =>
              [
                0 => 'xltx',
              ],
          ],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' =>
          [
            'desc' =>
              [
                0 => 'Word 2007 document',
              ],
            'e' =>
              [
                0 => 'docx',
              ],
          ],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.template' =>
          [
            'desc' =>
              [
                0 => 'Word 2007 document template',
              ],
            'e' =>
              [
                0 => 'dotx',
              ],
          ],
        'application/vnd.osgeo.mapguide.package' =>
          [
            'e' =>
              [
                0 => 'mgp',
              ],
          ],
        'application/vnd.osgi.dp' =>
          [
            'e' =>
              [
                0 => 'dp',
              ],
          ],
        'application/vnd.osgi.subsystem' =>
          [
            'e' =>
              [
                0 => 'esa',
              ],
          ],
        'application/vnd.palm' =>
          [
            'a' =>
              [
                0 => 'application/x-palm-database',
              ],
            'desc' =>
              [
                0 => 'Palm OS database',
              ],
            'e' =>
              [
                0 => 'pdb',
                1 => 'pqa',
                2 => 'oprc',
                3 => 'prc',
              ],
          ],
        'application/vnd.pawaafile' =>
          [
            'e' =>
              [
                0 => 'paw',
              ],
          ],
        'application/vnd.pg.format' =>
          [
            'e' =>
              [
                0 => 'str',
              ],
          ],
        'application/vnd.pg.osasli' =>
          [
            'e' =>
              [
                0 => 'ei6',
              ],
          ],
        'application/vnd.picsel' =>
          [
            'e' =>
              [
                0 => 'efif',
              ],
          ],
        'application/vnd.pmi.widget' =>
          [
            'e' =>
              [
                0 => 'wg',
              ],
          ],
        'application/vnd.pocketlearn' =>
          [
            'e' =>
              [
                0 => 'plf',
              ],
          ],
        'application/vnd.powerbuilder6' =>
          [
            'e' =>
              [
                0 => 'pbd',
              ],
          ],
        'application/vnd.previewsystems.box' =>
          [
            'e' =>
              [
                0 => 'box',
              ],
          ],
        'application/vnd.proteus.magazine' =>
          [
            'e' =>
              [
                0 => 'mgz',
              ],
          ],
        'application/vnd.publishare-delta-tree' =>
          [
            'e' =>
              [
                0 => 'qps',
              ],
          ],
        'application/vnd.pvi.ptid1' =>
          [
            'e' =>
              [
                0 => 'ptid',
              ],
          ],
        'application/vnd.quark.quarkxpress' =>
          [
            'desc' =>
              [
                0 => 'QuarkXPress document',
              ],
            'e' =>
              [
                0 => 'qxd',
                1 => 'qxt',
                2 => 'qwd',
                3 => 'qwt',
                4 => 'qxl',
                5 => 'qxb',
                6 => 'qxp',
              ],
          ],
        'application/vnd.rar' =>
          [
            'a' =>
              [
                0 => 'application/x-rar',
                1 => 'application/x-rar-compressed',
              ],
            'desc' =>
              [
                0 => 'RAR archive',
                1 => 'RAR: Roshal ARchive',
              ],
            'e' =>
              [
                0 => 'rar',
              ],
          ],
        'application/vnd.realvnc.bed' =>
          [
            'e' =>
              [
                0 => 'bed',
              ],
          ],
        'application/vnd.recordare.musicxml' =>
          [
            'e' =>
              [
                0 => 'mxl',
              ],
          ],
        'application/vnd.recordare.musicxml+xml' =>
          [
            'e' =>
              [
                0 => 'musicxml',
              ],
          ],
        'application/vnd.rig.cryptonote' =>
          [
            'e' =>
              [
                0 => 'cryptonote',
              ],
          ],
        'application/vnd.rim.cod' =>
          [
            'e' =>
              [
                0 => 'cod',
              ],
          ],
        'application/vnd.rn-realmedia' =>
          [
            'a' =>
              [
                0 => 'application/vnd.rn-realmedia-vbr',
              ],
            'desc' =>
              [
                0 => 'RealMedia document',
              ],
            'e' =>
              [
                0 => 'rm',
                1 => 'rmj',
                2 => 'rmm',
                3 => 'rms',
                4 => 'rmx',
                5 => 'rmvb',
              ],
          ],
        'application/vnd.route66.link66+xml' =>
          [
            'e' =>
              [
                0 => 'link66',
              ],
          ],
        'application/vnd.sailingtracker.track' =>
          [
            'e' =>
              [
                0 => 'st',
              ],
          ],
        'application/vnd.seemail' =>
          [
            'e' =>
              [
                0 => 'see',
              ],
          ],
        'application/vnd.sema' =>
          [
            'e' =>
              [
                0 => 'sema',
              ],
          ],
        'application/vnd.semd' =>
          [
            'e' =>
              [
                0 => 'semd',
              ],
          ],
        'application/vnd.semf' =>
          [
            'e' =>
              [
                0 => 'semf',
              ],
          ],
        'application/vnd.shana.informed.formdata' =>
          [
            'e' =>
              [
                0 => 'ifm',
              ],
          ],
        'application/vnd.shana.informed.formtemplate' =>
          [
            'e' =>
              [
                0 => 'itp',
              ],
          ],
        'application/vnd.shana.informed.interchange' =>
          [
            'e' =>
              [
                0 => 'iif',
              ],
          ],
        'application/vnd.shana.informed.package' =>
          [
            'e' =>
              [
                0 => 'ipk',
              ],
          ],
        'application/vnd.simtech-mindmapper' =>
          [
            'e' =>
              [
                0 => 'twd',
                1 => 'twds',
              ],
          ],
        'application/vnd.smaf' =>
          [
            'a' =>
              [
                0 => 'application/x-smaf',
              ],
            'desc' =>
              [
                0 => 'SMAF audio',
                1 => 'SMAF: Synthetic music Mobile Application Format',
              ],
            'e' =>
              [
                0 => 'mmf',
                1 => 'smaf',
              ],
          ],
        'application/vnd.smart.teacher' =>
          [
            'e' =>
              [
                0 => 'teacher',
              ],
          ],
        'application/vnd.snap' =>
          [
            'desc' =>
              [
                0 => 'Snap package',
              ],
            'e' =>
              [
                0 => 'snap',
              ],
          ],
        'application/vnd.solent.sdkm+xml' =>
          [
            'e' =>
              [
                0 => 'sdkm',
                1 => 'sdkd',
              ],
          ],
        'application/vnd.spotfire.dxp' =>
          [
            'e' =>
              [
                0 => 'dxp',
              ],
          ],
        'application/vnd.spotfire.sfs' =>
          [
            'e' =>
              [
                0 => 'sfs',
              ],
          ],
        'application/vnd.sqlite3' =>
          [
            'a' =>
              [
                0 => 'application/x-sqlite3',
              ],
            'desc' =>
              [
                0 => 'SQLite3 database',
              ],
            'e' =>
              [
                0 => 'sqlite3',
              ],
          ],
        'application/vnd.squashfs' =>
          [
            'desc' =>
              [
                0 => 'Squashfs filesystem image',
              ],
            'e' =>
              [
                0 => 'sfs',
                1 => 'sqfs',
                2 => 'sqsh',
                3 => 'squashfs',
              ],
          ],
        'application/vnd.stardivision.calc' =>
          [
            'desc' =>
              [
                0 => 'StarCalc 5 spreadsheet',
              ],
            'e' =>
              [
                0 => 'sdc',
              ],
          ],
        'application/vnd.stardivision.chart' =>
          [
            'desc' =>
              [
                0 => 'StarChart 5 chart',
              ],
            'e' =>
              [
                0 => 'sds',
              ],
          ],
        'application/vnd.stardivision.draw' =>
          [
            'desc' =>
              [
                0 => 'StarDraw 5 drawing',
              ],
            'e' =>
              [
                0 => 'sda',
              ],
          ],
        'application/vnd.stardivision.impress' =>
          [
            'desc' =>
              [
                0 => 'StarImpress 5 presentation',
              ],
            'e' =>
              [
                0 => 'sdd',
              ],
          ],
        'application/vnd.stardivision.impress-packed' =>
          [
            'desc' =>
              [
                0 => 'StarImpress packed presentation',
              ],
            'e' =>
              [
                0 => 'sdp',
              ],
          ],
        'application/vnd.stardivision.mail' =>
          [
            'desc' =>
              [
                0 => 'StarMail 5 email',
              ],
            'e' =>
              [
                0 => 'sdm',
              ],
          ],
        'application/vnd.stardivision.math' =>
          [
            'desc' =>
              [
                0 => 'StarMath 5 formula',
              ],
            'e' =>
              [
                0 => 'smf',
              ],
          ],
        'application/vnd.stardivision.writer' =>
          [
            'desc' =>
              [
                0 => 'StarWriter 5 document',
              ],
            'e' =>
              [
                0 => 'sdw',
                1 => 'vor',
              ],
          ],
        'application/vnd.stardivision.writer-global' =>
          [
            'desc' =>
              [
                0 => 'StarWriter 5 master document',
              ],
            'e' =>
              [
                0 => 'sgl',
              ],
          ],
        'application/vnd.stepmania.package' =>
          [
            'e' =>
              [
                0 => 'smzip',
              ],
          ],
        'application/vnd.stepmania.stepchart' =>
          [
            'e' =>
              [
                0 => 'sm',
              ],
          ],
        'application/vnd.sun.xml.calc' =>
          [
            'desc' =>
              [
                0 => 'OpenOffice.org 1.0 Calc spreadsheet',
              ],
            'e' =>
              [
                0 => 'sxc',
              ],
          ],
        'application/vnd.sun.xml.calc.template' =>
          [
            'desc' =>
              [
                0 => 'OpenOffice.org 1.0 Calc template',
              ],
            'e' =>
              [
                0 => 'stc',
              ],
          ],
        'application/vnd.sun.xml.draw' =>
          [
            'desc' =>
              [
                0 => 'OpenOffice.org 1.0 Draw drawing',
              ],
            'e' =>
              [
                0 => 'sxd',
              ],
          ],
        'application/vnd.sun.xml.draw.template' =>
          [
            'desc' =>
              [
                0 => 'OpenOffice.org 1.0 Draw template',
              ],
            'e' =>
              [
                0 => 'std',
              ],
          ],
        'application/vnd.sun.xml.impress' =>
          [
            'desc' =>
              [
                0 => 'OpenOffice.org 1.0 Impress presentation',
              ],
            'e' =>
              [
                0 => 'sxi',
              ],
          ],
        'application/vnd.sun.xml.impress.template' =>
          [
            'desc' =>
              [
                0 => 'OpenOffice.org 1.0 Impress template',
              ],
            'e' =>
              [
                0 => 'sti',
              ],
          ],
        'application/vnd.sun.xml.math' =>
          [
            'desc' =>
              [
                0 => 'OpenOffice.org 1.0 Math formula',
              ],
            'e' =>
              [
                0 => 'sxm',
              ],
          ],
        'application/vnd.sun.xml.writer' =>
          [
            'desc' =>
              [
                0 => 'OpenOffice.org 1.0 Writer document',
              ],
            'e' =>
              [
                0 => 'sxw',
              ],
          ],
        'application/vnd.sun.xml.writer.global' =>
          [
            'desc' =>
              [
                0 => 'OpenOffice.org 1.0 Writer global document',
              ],
            'e' =>
              [
                0 => 'sxg',
              ],
          ],
        'application/vnd.sun.xml.writer.template' =>
          [
            'desc' =>
              [
                0 => 'OpenOffice.org 1.0 Writer template',
              ],
            'e' =>
              [
                0 => 'stw',
              ],
          ],
        'application/vnd.sus-calendar' =>
          [
            'e' =>
              [
                0 => 'sus',
                1 => 'susp',
              ],
          ],
        'application/vnd.svd' =>
          [
            'e' =>
              [
                0 => 'svd',
              ],
          ],
        'application/vnd.symbian.install' =>
          [
            'desc' =>
              [
                0 => 'SIS package',
                1 => 'SIS: Symbian Installation File',
              ],
            'e' =>
              [
                0 => 'sis',
                1 => 'sisx',
              ],
          ],
        'application/vnd.syncml+xml' =>
          [
            'e' =>
              [
                0 => 'xsm',
              ],
          ],
        'application/vnd.syncml.dm+wbxml' =>
          [
            'e' =>
              [
                0 => 'bdm',
              ],
          ],
        'application/vnd.syncml.dm+xml' =>
          [
            'e' =>
              [
                0 => 'xdm',
              ],
          ],
        'application/vnd.tao.intent-module-archive' =>
          [
            'e' =>
              [
                0 => 'tao',
              ],
          ],
        'application/vnd.tcpdump.pcap' =>
          [
            'a' =>
              [
                0 => 'application/x-pcap',
                1 => 'application/pcap',
              ],
            'desc' =>
              [
                0 => 'Network packet capture',
              ],
            'e' =>
              [
                0 => 'pcap',
                1 => 'cap',
                2 => 'dmp',
              ],
          ],
        'application/vnd.tmobile-livetv' =>
          [
            'e' =>
              [
                0 => 'tmo',
              ],
          ],
        'application/vnd.trid.tpt' =>
          [
            'e' =>
              [
                0 => 'tpt',
              ],
          ],
        'application/vnd.triscape.mxs' =>
          [
            'e' =>
              [
                0 => 'mxs',
              ],
          ],
        'application/vnd.trueapp' =>
          [
            'e' =>
              [
                0 => 'tra',
              ],
          ],
        'application/vnd.ufdl' =>
          [
            'e' =>
              [
                0 => 'ufd',
                1 => 'ufdl',
              ],
          ],
        'application/vnd.uiq.theme' =>
          [
            'e' =>
              [
                0 => 'utz',
              ],
          ],
        'application/vnd.umajin' =>
          [
            'e' =>
              [
                0 => 'umj',
              ],
          ],
        'application/vnd.unity' =>
          [
            'e' =>
              [
                0 => 'unityweb',
              ],
          ],
        'application/vnd.uoml+xml' =>
          [
            'e' =>
              [
                0 => 'uoml',
              ],
          ],
        'application/vnd.vcx' =>
          [
            'e' =>
              [
                0 => 'vcx',
              ],
          ],
        'application/vnd.visio' =>
          [
            'desc' =>
              [
                0 => 'Microsoft Visio document',
              ],
            'e' =>
              [
                0 => 'vsd',
                1 => 'vst',
                2 => 'vss',
                3 => 'vsw',
              ],
          ],
        'application/vnd.visionary' =>
          [
            'e' =>
              [
                0 => 'vis',
              ],
          ],
        'application/vnd.vsf' =>
          [
            'e' =>
              [
                0 => 'vsf',
              ],
          ],
        'application/vnd.wap.wbxml' =>
          [
            'e' =>
              [
                0 => 'wbxml',
              ],
          ],
        'application/vnd.wap.wmlc' =>
          [
            'e' =>
              [
                0 => 'wmlc',
              ],
          ],
        'application/vnd.wap.wmlscriptc' =>
          [
            'e' =>
              [
                0 => 'wmlsc',
              ],
          ],
        'application/vnd.webturbo' =>
          [
            'e' =>
              [
                0 => 'wtb',
              ],
          ],
        'application/vnd.wolfram.player' =>
          [
            'e' =>
              [
                0 => 'nbp',
              ],
          ],
        'application/vnd.wordperfect' =>
          [
            'a' =>
              [
                0 => 'application/x-wordperfect',
                1 => 'application/wordperfect',
              ],
            'desc' =>
              [
                0 => 'WordPerfect document',
              ],
            'e' =>
              [
                0 => 'wpd',
                1 => 'wp',
                2 => 'wp4',
                3 => 'wp5',
                4 => 'wp6',
                5 => 'wpp',
              ],
          ],
        'application/vnd.wqd' =>
          [
            'e' =>
              [
                0 => 'wqd',
              ],
          ],
        'application/vnd.wt.stf' =>
          [
            'e' =>
              [
                0 => 'stf',
              ],
          ],
        'application/vnd.xara' =>
          [
            'e' =>
              [
                0 => 'xar',
              ],
          ],
        'application/vnd.xfdl' =>
          [
            'e' =>
              [
                0 => 'xfdl',
              ],
          ],
        'application/vnd.yamaha.hv-dic' =>
          [
            'e' =>
              [
                0 => 'hvd',
              ],
          ],
        'application/vnd.yamaha.hv-script' =>
          [
            'e' =>
              [
                0 => 'hvs',
              ],
          ],
        'application/vnd.yamaha.hv-voice' =>
          [
            'e' =>
              [
                0 => 'hvp',
              ],
          ],
        'application/vnd.yamaha.openscoreformat' =>
          [
            'e' =>
              [
                0 => 'osf',
              ],
          ],
        'application/vnd.yamaha.openscoreformat.osfpvg+xml' =>
          [
            'e' =>
              [
                0 => 'osfpvg',
              ],
          ],
        'application/vnd.yamaha.smaf-audio' =>
          [
            'e' =>
              [
                0 => 'saf',
              ],
          ],
        'application/vnd.yamaha.smaf-phrase' =>
          [
            'e' =>
              [
                0 => 'spf',
              ],
          ],
        'application/vnd.yellowriver-custom-menu' =>
          [
            'e' =>
              [
                0 => 'cmp',
              ],
          ],
        'application/vnd.zul' =>
          [
            'e' =>
              [
                0 => 'zir',
                1 => 'zirz',
              ],
          ],
        'application/vnd.zzazz.deck+xml' =>
          [
            'e' =>
              [
                0 => 'zaz',
              ],
          ],
        'application/voicexml+xml' =>
          [
            'e' =>
              [
                0 => 'vxml',
              ],
          ],
        'application/wasm' =>
          [
            'desc' =>
              [
                0 => 'WASM binary module',
                1 => 'WASM: Web Assembly',
              ],
            'e' =>
              [
                0 => 'wasm',
              ],
          ],
        'application/widget' =>
          [
            'e' =>
              [
                0 => 'wgt',
              ],
          ],
        'application/winhlp' =>
          [
            'a' =>
              [
                0 => 'zz-application/zz-winassoc-hlp',
              ],
            'desc' =>
              [
                0 => 'WinHelp help file',
              ],
            'e' =>
              [
                0 => 'hlp',
              ],
          ],
        'application/wsdl+xml' =>
          [
            'e' =>
              [
                0 => 'wsdl',
              ],
          ],
        'application/wspolicy+xml' =>
          [
            'e' =>
              [
                0 => 'wspolicy',
              ],
          ],
        'application/x-7z-compressed' =>
          [
            'desc' =>
              [
                0 => '7-zip archive',
              ],
            'e' =>
              [
                0 => '7z',
                1 => '7z.001',
              ],
          ],
        'application/x-abiword' =>
          [
            'desc' =>
              [
                0 => 'AbiWord document',
              ],
            'e' =>
              [
                0 => 'abw',
                1 => 'abw.crashed',
                2 => 'abw.gz',
                3 => 'zabw',
              ],
          ],
        'application/x-ace' =>
          [
            'desc' =>
              [
                0 => 'ACE archive',
              ],
            'e' =>
              [
                0 => 'ace',
              ],
          ],
        'application/x-ace-compressed' =>
          [
            'e' =>
              [
                0 => 'ace',
              ],
          ],
        'application/x-alz' =>
          [
            'desc' =>
              [
                0 => 'Alzip archive',
              ],
            'e' =>
              [
                0 => 'alz',
              ],
          ],
        'application/x-amiga-disk-format' =>
          [
            'desc' =>
              [
                0 => 'Amiga disk image',
              ],
            'e' =>
              [
                0 => 'adf',
              ],
          ],
        'application/x-amipro' =>
          [
            'desc' =>
              [
                0 => 'Lotus AmiPro document',
              ],
            'e' =>
              [
                0 => 'sam',
              ],
          ],
        'application/x-aportisdoc' =>
          [
            'desc' =>
              [
                0 => 'AportisDoc document',
              ],
            'e' =>
              [
                0 => 'pdb',
                1 => 'pdc',
              ],
          ],
        'application/x-apple-diskimage' =>
          [
            'desc' =>
              [
                0 => 'Apple disk image',
              ],
            'e' =>
              [
                0 => 'dmg',
              ],
          ],
        'application/x-apple-systemprofiler+xml' =>
          [
            'desc' =>
              [
                0 => 'Apple System Profiler',
              ],
            'e' =>
              [
                0 => 'spx',
              ],
          ],
        'application/x-appleworks-document' =>
          [
            'desc' =>
              [
                0 => 'AppleWorks document',
              ],
            'e' =>
              [
                0 => 'cwk',
              ],
          ],
        'application/x-applix-spreadsheet' =>
          [
            'desc' =>
              [
                0 => 'Applix Spreadsheets spreadsheet',
              ],
            'e' =>
              [
                0 => 'as',
              ],
          ],
        'application/x-applix-word' =>
          [
            'desc' =>
              [
                0 => 'Applix Words document',
              ],
            'e' =>
              [
                0 => 'aw',
              ],
          ],
        'application/x-archive' =>
          [
            'desc' =>
              [
                0 => 'AR archive',
              ],
            'e' =>
              [
                0 => 'a',
                1 => 'ar',
                2 => 'lib',
              ],
          ],
        'application/x-arj' =>
          [
            'desc' =>
              [
                0 => 'ARJ archive',
                1 => 'ARJ: Archived by Robert Jung',
              ],
            'e' =>
              [
                0 => 'arj',
              ],
          ],
        'application/x-asar' =>
          [
            'desc' =>
              [
                0 => 'Electron Archive (ASAR)',
                1 => 'ASAR: Atom Shell Archive Format',
              ],
            'e' =>
              [
                0 => 'asar',
              ],
          ],
        'application/x-asp' =>
          [
            'desc' =>
              [
                0 => 'ASP page',
                1 => 'ASP: Active Server Page',
              ],
            'e' =>
              [
                0 => 'asp',
              ],
          ],
        'application/x-atari-2600-rom' =>
          [
            'desc' =>
              [
                0 => 'Atari 2600 ROM',
              ],
            'e' =>
              [
                0 => 'a26',
              ],
          ],
        'application/x-atari-7800-rom' =>
          [
            'desc' =>
              [
                0 => 'Atari 7800 ROM',
              ],
            'e' =>
              [
                0 => 'a78',
              ],
          ],
        'application/x-atari-lynx-rom' =>
          [
            'desc' =>
              [
                0 => 'Atari Lynx ROM',
              ],
            'e' =>
              [
                0 => 'lnx',
              ],
          ],
        'application/x-authorware-bin' =>
          [
            'e' =>
              [
                0 => 'aab',
                1 => 'x32',
                2 => 'u32',
                3 => 'vox',
              ],
          ],
        'application/x-authorware-map' =>
          [
            'e' =>
              [
                0 => 'aam',
              ],
          ],
        'application/x-authorware-seg' =>
          [
            'e' =>
              [
                0 => 'aas',
              ],
          ],
        'application/x-awk' =>
          [
            'desc' =>
              [
                0 => 'AWK script',
              ],
            'e' =>
              [
                0 => 'awk',
              ],
          ],
        'application/x-bat' =>
          [
            'a' =>
              [
                0 => 'application/bat',
              ],
            'desc' =>
              [
                0 => 'DOS/Windows batch file',
              ],
            'e' =>
              [
                0 => 'bat',
              ],
          ],
        'application/x-bcpio' =>
          [
            'desc' =>
              [
                0 => 'BCPIO archive',
                1 => 'BCPIO: Binary CPIO',
              ],
            'e' =>
              [
                0 => 'bcpio',
              ],
          ],
        'application/x-bittorrent' =>
          [
            'desc' =>
              [
                0 => 'BitTorrent seed file',
              ],
            'e' =>
              [
                0 => 'torrent',
              ],
          ],
        'application/x-blender' =>
          [
            'desc' =>
              [
                0 => 'Blender scene',
              ],
            'e' =>
              [
                0 => 'blend',
                1 => 'blender',
              ],
          ],
        'application/x-blorb' =>
          [
            'e' =>
              [
                0 => 'blb',
                1 => 'blorb',
              ],
          ],
        'application/x-bps-patch' =>
          [
            'desc' =>
              [
                0 => 'BPS patch',
                1 => 'BPS: Binary Patching System',
              ],
            'e' =>
              [
                0 => 'bps',
              ],
          ],
        'application/x-bsdiff' =>
          [
            'desc' =>
              [
                0 => 'Binary differences between files',
              ],
            'e' =>
              [
                0 => 'bsdiff',
              ],
          ],
        'application/x-bzdvi' =>
          [
            'desc' =>
              [
                0 => 'TeX DVI document (bzip2-compressed)',
              ],
            'e' =>
              [
                0 => 'dvi.bz2',
              ],
          ],
        'application/x-bzip1' =>
          [
            'desc' =>
              [
                0 => 'Bzip1 archive',
              ],
            'e' =>
              [
                0 => 'bz',
              ],
          ],
        'application/x-bzip1-compressed-tar' =>
          [
            'desc' =>
              [
                0 => 'Tar archive (bzip1-compressed)',
              ],
            'e' =>
              [
                0 => 'tar.bz',
                1 => 'tbz',
              ],
          ],
        'application/x-bzip2' =>
          [
            'a' =>
              [
                0 => 'application/bzip2',
                1 => 'application/x-bzip',
              ],
            'desc' =>
              [
                0 => 'Bzip2 archive',
              ],
            'e' =>
              [
                0 => 'bz2',
                1 => 'boz',
              ],
          ],
        'application/x-bzip2-compressed-tar' =>
          [
            'a' =>
              [
                0 => 'application/x-bzip-compressed-tar',
              ],
            'desc' =>
              [
                0 => 'Tar archive (bzip2-compressed)',
              ],
            'e' =>
              [
                0 => 'tar.bz2',
                1 => 'tbz2',
                2 => 'tb2',
              ],
          ],
        'application/x-bzip3' =>
          [
            'desc' =>
              [
                0 => 'Bzip3 archive',
              ],
            'e' =>
              [
                0 => 'bz3',
              ],
          ],
        'application/x-bzip3-compressed-tar' =>
          [
            'desc' =>
              [
                0 => 'Tar archive (bzip3-compressed)',
              ],
            'e' =>
              [
                0 => 'tar.bz3',
                1 => 'tbz3',
              ],
          ],
        'application/x-bzpdf' =>
          [
            'desc' =>
              [
                0 => 'PDF document (bzip2-compressed)',
              ],
            'e' =>
              [
                0 => 'pdf.bz2',
              ],
          ],
        'application/x-bzpostscript' =>
          [
            'desc' =>
              [
                0 => 'PostScript document (bzip2-compressed)',
              ],
            'e' =>
              [
                0 => 'ps.bz2',
              ],
          ],
        'application/x-cb7' =>
          [
            'desc' =>
              [
                0 => 'Comic book archive (7z container)',
              ],
            'e' =>
              [
                0 => 'cb7',
              ],
          ],
        'application/x-cbt' =>
          [
            'desc' =>
              [
                0 => 'Comic book archive (tar container)',
              ],
            'e' =>
              [
                0 => 'cbt',
              ],
          ],
        'application/x-ccmx' =>
          [
            'desc' =>
              [
                0 => 'CCMX color correction file',
              ],
            'e' =>
              [
                0 => 'ccmx',
              ],
          ],
        'application/x-cdlink' =>
          [
            'e' =>
              [
                0 => 'vcd',
              ],
          ],
        'application/x-cdrdao-toc' =>
          [
            'desc' =>
              [
                0 => 'CD Table Of Contents',
              ],
            'e' =>
              [
                0 => 'toc',
              ],
          ],
        'application/x-cfs-compressed' =>
          [
            'e' =>
              [
                0 => 'cfs',
              ],
          ],
        'application/x-chat' =>
          [
            'e' =>
              [
                0 => 'chat',
              ],
          ],
        'application/x-cisco-vpn-settings' =>
          [
            'desc' =>
              [
                0 => 'Cisco VPN settings',
              ],
            'e' =>
              [
                0 => 'pcf',
              ],
          ],
        'application/x-compress' =>
          [
            'desc' =>
              [
                0 => 'UNIX-compressed file',
              ],
            'e' =>
              [
                0 => 'z',
              ],
          ],
        'application/x-compressed-iso' =>
          [
            'desc' =>
              [
                0 => 'Compressed CD image',
              ],
            'e' =>
              [
                0 => 'cso',
              ],
          ],
        'application/x-compressed-tar' =>
          [
            'desc' =>
              [
                0 => 'Tar archive (gzip-compressed)',
              ],
            'e' =>
              [
                0 => 'tar.gz',
                1 => 'tgz',
              ],
          ],
        'application/x-conference' =>
          [
            'e' =>
              [
                0 => 'nsc',
              ],
          ],
        'application/x-cpio' =>
          [
            'desc' =>
              [
                0 => 'CPIO archive',
              ],
            'e' =>
              [
                0 => 'cpio',
              ],
          ],
        'application/x-cpio-compressed' =>
          [
            'desc' =>
              [
                0 => 'CPIO archive (gzip-compressed)',
              ],
            'e' =>
              [
                0 => 'cpio.gz',
              ],
          ],
        'application/x-csh' =>
          [
            'desc' =>
              [
                0 => 'C shell script',
              ],
            'e' =>
              [
                0 => 'csh',
              ],
          ],
        'application/x-cue' =>
          [
            'desc' =>
              [
                0 => 'CD image cuesheet',
              ],
            'e' =>
              [
                0 => 'cue',
              ],
          ],
        'application/x-dar' =>
          [
            'desc' =>
              [
                0 => 'DAR archive',
                1 => 'DAR: Disk ARchive',
              ],
            'e' =>
              [
                0 => 'dar',
              ],
          ],
        'application/x-designer' =>
          [
            'desc' =>
              [
                0 => 'Qt Designer interface document',
              ],
            'e' =>
              [
                0 => 'ui',
              ],
          ],
        'application/x-desktop' =>
          [
            'a' =>
              [
                0 => 'application/x-gnome-app-info',
              ],
            'desc' =>
              [
                0 => 'Desktop entry',
              ],
            'e' =>
              [
                0 => 'desktop',
                1 => 'kdelnk',
              ],
          ],
        'application/x-dgc-compressed' =>
          [
            'e' =>
              [
                0 => 'dgc',
              ],
          ],
        'application/x-dia-diagram' =>
          [
            'desc' =>
              [
                0 => 'Dia diagram',
              ],
            'e' =>
              [
                0 => 'dia',
              ],
          ],
        'application/x-dia-shape' =>
          [
            'desc' =>
              [
                0 => 'Dia shape',
              ],
            'e' =>
              [
                0 => 'shape',
              ],
          ],
        'application/x-director' =>
          [
            'e' =>
              [
                0 => 'dir',
                1 => 'dcr',
                2 => 'dxr',
                3 => 'cst',
                4 => 'cct',
                5 => 'cxt',
                6 => 'w3d',
                7 => 'fgd',
                8 => 'swa',
              ],
          ],
        'application/x-discjuggler-cd-image' =>
          [
            'desc' =>
              [
                0 => 'Padus DiscJuggler CD image',
              ],
            'e' =>
              [
                0 => 'cdi',
              ],
          ],
        'application/x-doom' =>
          [
            'e' =>
              [
                0 => 'wad',
              ],
          ],
        'application/x-doom-wad' =>
          [
            'desc' =>
              [
                0 => 'Doom WAD file',
                1 => 'WAD: Where\'s All the Data',
              ],
            'e' =>
              [
                0 => 'wad',
              ],
          ],
        'application/x-dosexec' =>
          [
            'desc' =>
              [
                0 => 'DOS executable',
                1 => 'DOS: Disk Operating System',
              ],
            'e' =>
              [
                0 => 'exe',
              ],
          ],
        'application/x-dreamcast-rom' =>
          [
            'desc' =>
              [
                0 => 'Dreamcast disc image',
              ],
            'e' =>
              [
                0 => 'iso',
              ],
          ],
        'application/x-dtbncx+xml' =>
          [
            'e' =>
              [
                0 => 'ncx',
              ],
          ],
        'application/x-dtbook+xml' =>
          [
            'e' =>
              [
                0 => 'dtb',
              ],
          ],
        'application/x-dtbresource+xml' =>
          [
            'e' =>
              [
                0 => 'res',
              ],
          ],
        'application/x-dvi' =>
          [
            'desc' =>
              [
                0 => 'TeX DVI document',
                1 => 'DVI: Device independent file format',
              ],
            'e' =>
              [
                0 => 'dvi',
              ],
          ],
        'application/x-e-theme' =>
          [
            'desc' =>
              [
                0 => 'Enlightenment theme',
              ],
            'e' =>
              [
                0 => 'etheme',
              ],
          ],
        'application/x-egon' =>
          [
            'desc' =>
              [
                0 => 'Egon Animator animation',
              ],
            'e' =>
              [
                0 => 'egon',
              ],
          ],
        'application/x-envoy' =>
          [
            'e' =>
              [
                0 => 'evy',
              ],
          ],
        'application/x-eris-link+cbor' =>
          [
            'desc' =>
              [
                0 => 'ERIS Link',
                1 => 'ERIS: Encoding for Robust Immutable Storage',
              ],
            'e' =>
              [
                0 => 'eris',
              ],
          ],
        'application/x-eva' =>
          [
            'e' =>
              [
                0 => 'eva',
              ],
          ],
        'application/x-excellon' =>
          [
            'desc' =>
              [
                0 => 'Excellon drill file',
              ],
            'e' =>
              [
                0 => 'drl',
              ],
          ],
        'application/x-fds-disk' =>
          [
            'desc' =>
              [
                0 => 'Nintendo FDS disk image',
                1 => 'FDS: Famicom Disk System',
              ],
            'e' =>
              [
                0 => 'fds',
              ],
          ],
        'application/x-fictionbook+xml' =>
          [
            'a' =>
              [
                0 => 'application/x-fictionbook',
              ],
            'desc' =>
              [
                0 => 'FictionBook document',
              ],
            'e' =>
              [
                0 => 'fb2',
              ],
          ],
        'application/x-fishscript' =>
          [
            'a' =>
              [
                0 => 'text/x-fish',
              ],
            'desc' =>
              [
                0 => 'Fish shell script',
              ],
            'e' =>
              [
                0 => 'fish',
              ],
          ],
        'application/x-fluid' =>
          [
            'desc' =>
              [
                0 => 'FLTK Fluid file',
                1 => 'FLTK: Fast Light Toolkit',
              ],
            'e' =>
              [
                0 => 'fl',
              ],
          ],
        'application/x-font-afm' =>
          [
            'desc' =>
              [
                0 => 'Adobe font metrics',
              ],
            'e' =>
              [
                0 => 'afm',
              ],
          ],
        'application/x-font-bdf' =>
          [
            'desc' =>
              [
                0 => 'BDF font',
              ],
            'e' =>
              [
                0 => 'bdf',
              ],
          ],
        'application/x-font-ghostscript' =>
          [
            'e' =>
              [
                0 => 'gsf',
              ],
          ],
        'application/x-font-linux-psf' =>
          [
            'desc' =>
              [
                0 => 'Linux PSF console font',
                1 => 'PSF: PC Screen Font',
              ],
            'e' =>
              [
                0 => 'psf',
              ],
          ],
        'application/x-font-pcf' =>
          [
            'desc' =>
              [
                0 => 'PCF font',
                1 => 'PCF: Portable Compiled Format',
              ],
            'e' =>
              [
                0 => 'pcf',
                1 => 'pcf.z',
                2 => 'pcf.gz',
              ],
          ],
        'application/x-font-snf' =>
          [
            'e' =>
              [
                0 => 'snf',
              ],
          ],
        'application/x-font-speedo' =>
          [
            'desc' =>
              [
                0 => 'Speedo font',
              ],
            'e' =>
              [
                0 => 'spd',
              ],
          ],
        'application/x-font-ttx' =>
          [
            'desc' =>
              [
                0 => 'TrueType XML font',
              ],
            'e' =>
              [
                0 => 'ttx',
              ],
          ],
        'application/x-font-type1' =>
          [
            'desc' =>
              [
                0 => 'PostScript type-1 font',
              ],
            'e' =>
              [
                0 => 'pfa',
                1 => 'pfb',
                2 => 'pfm',
                3 => 'afm',
                4 => 'gsf',
              ],
          ],
        'application/x-freearc' =>
          [
            'e' =>
              [
                0 => 'arc',
              ],
          ],
        'application/x-futuresplash' =>
          [
            'e' =>
              [
                0 => 'spl',
              ],
          ],
        'application/x-gameboy-color-rom' =>
          [
            'desc' =>
              [
                0 => 'Game Boy Color ROM',
              ],
            'e' =>
              [
                0 => 'gbc',
                1 => 'cgb',
              ],
          ],
        'application/x-gameboy-rom' =>
          [
            'desc' =>
              [
                0 => 'Game Boy ROM',
              ],
            'e' =>
              [
                0 => 'gb',
                1 => 'sgb',
              ],
          ],
        'application/x-gamecube-rom' =>
          [
            'a' =>
              [
                0 => 'application/x-gamecube-iso-image',
              ],
            'desc' =>
              [
                0 => 'GameCube disc image',
              ],
            'e' =>
              [
                0 => 'iso',
              ],
          ],
        'application/x-gamegear-rom' =>
          [
            'desc' =>
              [
                0 => 'Game Gear ROM',
              ],
            'e' =>
              [
                0 => 'gg',
              ],
          ],
        'application/x-gba-rom' =>
          [
            'desc' =>
              [
                0 => 'Game Boy Advance ROM',
              ],
            'e' =>
              [
                0 => 'gba',
                1 => 'agb',
              ],
          ],
        'application/x-gca-compressed' =>
          [
            'e' =>
              [
                0 => 'gca',
              ],
          ],
        'application/x-gd-rom-cue' =>
          [
            'desc' =>
              [
                0 => 'GD-ROM image cuesheet',
              ],
            'e' =>
              [
                0 => 'gdi',
              ],
          ],
        'application/x-gdscript' =>
          [
            'desc' =>
              [
                0 => 'GDScript script',
              ],
            'e' =>
              [
                0 => 'gd',
              ],
          ],
        'application/x-genesis-32x-rom' =>
          [
            'desc' =>
              [
                0 => 'Genesis 32X ROM',
              ],
            'e' =>
              [
                0 => '32x',
                1 => 'mdx',
              ],
          ],
        'application/x-genesis-rom' =>
          [
            'desc' =>
              [
                0 => 'Genesis ROM',
              ],
            'e' =>
              [
                0 => 'gen',
                1 => 'smd',
                2 => 'md',
                3 => 'sgd',
              ],
          ],
        'application/x-gerber-job' =>
          [
            'desc' =>
              [
                0 => 'Gerber job file',
              ],
            'e' =>
              [
                0 => 'gbrjob',
              ],
          ],
        'application/x-gettext-translation' =>
          [
            'desc' =>
              [
                0 => 'Translated messages (machine-readable)',
              ],
            'e' =>
              [
                0 => 'gmo',
                1 => 'mo',
              ],
          ],
        'application/x-glade' =>
          [
            'desc' =>
              [
                0 => 'Glade project',
              ],
            'e' =>
              [
                0 => 'glade',
              ],
          ],
        'application/x-glulx' =>
          [
            'e' =>
              [
                0 => 'ulx',
              ],
          ],
        'application/x-gnucash' =>
          [
            'desc' =>
              [
                0 => 'GnuCash financial data',
              ],
            'e' =>
              [
                0 => 'gnucash',
                1 => 'gnc',
                2 => 'xac',
              ],
          ],
        'application/x-gnumeric' =>
          [
            'desc' =>
              [
                0 => 'Gnumeric spreadsheet',
              ],
            'e' =>
              [
                0 => 'gnumeric',
              ],
          ],
        'application/x-gnuplot' =>
          [
            'desc' =>
              [
                0 => 'Gnuplot document',
              ],
            'e' =>
              [
                0 => 'gp',
                1 => 'gplt',
                2 => 'gnuplot',
              ],
          ],
        'application/x-go-sgf' =>
          [
            'desc' =>
              [
                0 => 'SGF record',
                1 => 'SGF: Smart Game Format',
              ],
            'e' =>
              [
                0 => 'sgf',
              ],
          ],
        'application/x-godot-resource' =>
          [
            'desc' =>
              [
                0 => 'Godot Engine resource',
              ],
            'e' =>
              [
                0 => 'res',
                1 => 'tres',
              ],
          ],
        'application/x-godot-scene' =>
          [
            'desc' =>
              [
                0 => 'Godot Engine scene',
              ],
            'e' =>
              [
                0 => 'scn',
                1 => 'tscn',
                2 => 'escn',
              ],
          ],
        'application/x-godot-shader' =>
          [
            'desc' =>
              [
                0 => 'Godot Engine shader',
              ],
            'e' =>
              [
                0 => 'gdshader',
              ],
          ],
        'application/x-gramps-xml' =>
          [
            'e' =>
              [
                0 => 'gramps',
              ],
          ],
        'application/x-graphite' =>
          [
            'desc' =>
              [
                0 => 'Graphite scientific graph',
              ],
            'e' =>
              [
                0 => 'gra',
              ],
          ],
        'application/x-gtk-builder' =>
          [
            'desc' =>
              [
                0 => 'GTK+ Builder interface document',
              ],
            'e' =>
              [
                0 => 'ui',
              ],
          ],
        'application/x-gz-font-linux-psf' =>
          [
            'desc' =>
              [
                0 => 'Linux PSF console font (gzip-compressed)',
                1 => 'PSF: PC Screen Font',
              ],
            'e' =>
              [
                0 => 'psf.gz',
              ],
          ],
        'application/x-gzdvi' =>
          [
            'desc' =>
              [
                0 => 'TeX DVI document (gzip-compressed)',
              ],
            'e' =>
              [
                0 => 'dvi.gz',
              ],
          ],
        'application/x-gzpdf' =>
          [
            'desc' =>
              [
                0 => 'PDF document (gzip-compressed)',
              ],
            'e' =>
              [
                0 => 'pdf.gz',
              ],
          ],
        'application/x-gzpostscript' =>
          [
            'desc' =>
              [
                0 => 'PostScript document (gzip-compressed)',
              ],
            'e' =>
              [
                0 => 'ps.gz',
              ],
          ],
        'application/x-hdf' =>
          [
            'desc' =>
              [
                0 => 'HDF document',
                1 => 'HDF: Hierarchical Data Format',
              ],
            'e' =>
              [
                0 => 'hdf',
                1 => 'hdf4',
                2 => 'h4',
                3 => 'hdf5',
                4 => 'h5',
              ],
          ],
        'application/x-hfe-floppy-image' =>
          [
            'a' =>
              [
                0 => 'application/x-hfe-file',
              ],
            'desc' =>
              [
                0 => 'HFE floppy disk image',
                1 => 'HFE: HxC Floppy Emulator',
              ],
            'e' =>
              [
                0 => 'hfe',
              ],
          ],
        'application/x-hwp' =>
          [
            'a' =>
              [
                0 => 'application/vnd.haansoft-hwp',
              ],
            'desc' =>
              [
                0 => 'Haansoft Hangul document',
              ],
            'e' =>
              [
                0 => 'hwp',
              ],
          ],
        'application/x-hwt' =>
          [
            'a' =>
              [
                0 => 'application/vnd.haansoft-hwt',
              ],
            'desc' =>
              [
                0 => 'Haansoft Hangul document template',
              ],
            'e' =>
              [
                0 => 'hwt',
              ],
          ],
        'application/x-ica' =>
          [
            'desc' =>
              [
                0 => 'Citrix ICA settings file',
                1 => 'ICA: Independent Computing Architecture',
              ],
            'e' =>
              [
                0 => 'ica',
              ],
          ],
        'application/x-install-instructions' =>
          [
            'e' =>
              [
                0 => 'install',
              ],
          ],
        'application/x-ips-patch' =>
          [
            'desc' =>
              [
                0 => 'IPS patch',
                1 => 'IPS: International Patching System',
              ],
            'e' =>
              [
                0 => 'ips',
              ],
          ],
        'application/x-ipynb+json' =>
          [
            'desc' =>
              [
                0 => 'Jupyter notebook document',
              ],
            'e' =>
              [
                0 => 'ipynb',
              ],
          ],
        'application/x-iso9660-appimage' =>
          [
            'desc' =>
              [
                0 => 'AppImage application bundle',
              ],
            'e' =>
              [
                0 => 'appimage',
              ],
          ],
        'application/x-it87' =>
          [
            'desc' =>
              [
                0 => 'IT 8.7 color calibration file',
              ],
            'e' =>
              [
                0 => 'it87',
              ],
          ],
        'application/x-java' =>
          [
            'a' =>
              [
                0 => 'application/java',
                1 => 'application/java-byte-code',
                2 => 'application/java-vm',
                3 => 'application/x-java-class',
                4 => 'application/x-java-vm',
              ],
            'desc' =>
              [
                0 => 'Java class',
              ],
            'e' =>
              [
                0 => 'class',
              ],
          ],
        'application/x-java-jce-keystore' =>
          [
            'desc' =>
              [
                0 => 'Java JCE keystore',
                1 => 'JCE: Java Cryptography Extension',
              ],
            'e' =>
              [
                0 => 'jceks',
              ],
          ],
        'application/x-java-jnlp-file' =>
          [
            'desc' =>
              [
                0 => 'JNLP file',
                1 => 'JNLP: Java Network Launching Protocol',
              ],
            'e' =>
              [
                0 => 'jnlp',
              ],
          ],
        'application/x-java-keystore' =>
          [
            'desc' =>
              [
                0 => 'Java keystore',
              ],
            'e' =>
              [
                0 => 'jks',
                1 => 'ks',
              ],
          ],
        'application/x-java-pack200' =>
          [
            'desc' =>
              [
                0 => 'Pack200 Java archive',
              ],
            'e' =>
              [
                0 => 'pack',
              ],
          ],
        'application/x-jbuilder-project' =>
          [
            'desc' =>
              [
                0 => 'JBuilder project',
              ],
            'e' =>
              [
                0 => 'jpr',
                1 => 'jpx',
              ],
          ],
        'application/x-karbon' =>
          [
            'desc' =>
              [
                0 => 'Karbon14 drawing',
              ],
            'e' =>
              [
                0 => 'karbon',
              ],
          ],
        'application/x-kchart' =>
          [
            'desc' =>
              [
                0 => 'KChart chart',
              ],
            'e' =>
              [
                0 => 'chrt',
              ],
          ],
        'application/x-kexi-connectiondata' =>
          [
            'desc' =>
              [
                0 => 'Kexi settings',
              ],
            'e' =>
              [
                0 => 'kexic',
              ],
          ],
        'application/x-kexiproject-shortcut' =>
          [
            'desc' =>
              [
                0 => 'Kexi shortcut',
              ],
            'e' =>
              [
                0 => 'kexis',
              ],
          ],
        'application/x-kexiproject-sqlite2' =>
          [
            'desc' =>
              [
                0 => 'Kexi database file',
              ],
            'e' =>
              [
                0 => 'kexi',
              ],
          ],
        'application/x-kexiproject-sqlite3' =>
          [
            'a' =>
              [
                0 => 'application/x-vnd.kde.kexi',
                1 => 'application/x-kexiproject-sqlite',
              ],
            'desc' =>
              [
                0 => 'Kexi database file',
              ],
            'e' =>
              [
                0 => 'kexi',
              ],
          ],
        'application/x-kformula' =>
          [
            'desc' =>
              [
                0 => 'KFormula formula',
              ],
            'e' =>
              [
                0 => 'kfo',
              ],
          ],
        'application/x-killustrator' =>
          [
            'desc' =>
              [
                0 => 'KIllustrator drawing',
              ],
            'e' =>
              [
                0 => 'kil',
              ],
          ],
        'application/x-kivio' =>
          [
            'desc' =>
              [
                0 => 'Kivio flowchart',
              ],
            'e' =>
              [
                0 => 'flw',
              ],
          ],
        'application/x-kontour' =>
          [
            'desc' =>
              [
                0 => 'Kontour drawing',
              ],
            'e' =>
              [
                0 => 'kon',
              ],
          ],
        'application/x-kpovmodeler' =>
          [
            'desc' =>
              [
                0 => 'KPovModeler scene',
              ],
            'e' =>
              [
                0 => 'kpm',
              ],
          ],
        'application/x-kpresenter' =>
          [
            'desc' =>
              [
                0 => 'KPresenter presentation',
              ],
            'e' =>
              [
                0 => 'kpr',
                1 => 'kpt',
              ],
          ],
        'application/x-krita' =>
          [
            'desc' =>
              [
                0 => 'Krita document',
              ],
            'e' =>
              [
                0 => 'kra',
                1 => 'krz',
              ],
          ],
        'application/x-kspread' =>
          [
            'desc' =>
              [
                0 => 'KSpread spreadsheet',
              ],
            'e' =>
              [
                0 => 'ksp',
              ],
          ],
        'application/x-kugar' =>
          [
            'desc' =>
              [
                0 => 'Kugar document',
              ],
            'e' =>
              [
                0 => 'kud',
              ],
          ],
        'application/x-kword' =>
          [
            'desc' =>
              [
                0 => 'KWord document',
              ],
            'e' =>
              [
                0 => 'kwd',
                1 => 'kwt',
              ],
          ],
        'application/x-latex' =>
          [
            'e' =>
              [
                0 => 'latex',
              ],
          ],
        'application/x-lha' =>
          [
            'a' =>
              [
                0 => 'application/x-lzh-compressed',
              ],
            'desc' =>
              [
                0 => 'LHA archive',
              ],
            'e' =>
              [
                0 => 'lha',
                1 => 'lzh',
              ],
          ],
        'application/x-lhz' =>
          [
            'desc' =>
              [
                0 => 'LHZ archive',
              ],
            'e' =>
              [
                0 => 'lhz',
              ],
          ],
        'application/x-lmdb' =>
          [
            'desc' =>
              [
                0 => 'LMDB database',
                1 => 'LMDB: Lightning Memory-Mapped Database',
              ],
            'e' =>
              [
                0 => 'mdb',
                1 => 'lmdb',
              ],
          ],
        'application/x-lrzip' =>
          [
            'desc' =>
              [
                0 => 'Lrzip archive',
                1 => 'Lrzip: Long Range Zip',
              ],
            'e' =>
              [
                0 => 'lrz',
              ],
          ],
        'application/x-lrzip-compressed-tar' =>
          [
            'desc' =>
              [
                0 => 'Tar archive (lrzip-compressed)',
              ],
            'e' =>
              [
                0 => 'tar.lrz',
                1 => 'tlrz',
              ],
          ],
        'application/x-lyx' =>
          [
            'a' =>
              [
                0 => 'text/x-lyx',
              ],
            'desc' =>
              [
                0 => 'LyX document',
              ],
            'e' =>
              [
                0 => 'lyx',
              ],
          ],
        'application/x-lz4' =>
          [
            'desc' =>
              [
                0 => 'LZ4 archive',
              ],
            'e' =>
              [
                0 => 'lz4',
              ],
          ],
        'application/x-lz4-compressed-tar' =>
          [
            'desc' =>
              [
                0 => 'Tar archive (LZ4-compressed)',
              ],
            'e' =>
              [
                0 => 'tar.lz4',
              ],
          ],
        'application/x-lzip' =>
          [
            'desc' =>
              [
                0 => 'Lzip archive',
              ],
            'e' =>
              [
                0 => 'lz',
              ],
          ],
        'application/x-lzip-compressed-tar' =>
          [
            'desc' =>
              [
                0 => 'Tar archive (lzip-compressed)',
              ],
            'e' =>
              [
                0 => 'tar.lz',
              ],
          ],
        'application/x-lzma' =>
          [
            'desc' =>
              [
                0 => 'LZMA archive',
                1 => 'LZMA: Lempel-Ziv-Markov chain-Algorithm',
              ],
            'e' =>
              [
                0 => 'lzma',
              ],
          ],
        'application/x-lzma-compressed-tar' =>
          [
            'desc' =>
              [
                0 => 'Tar archive (LZMA-compressed)',
              ],
            'e' =>
              [
                0 => 'tar.lzma',
                1 => 'tlz',
              ],
          ],
        'application/x-lzop' =>
          [
            'desc' =>
              [
                0 => 'LZO archive',
                1 => 'LZO: Lempel-Ziv-Oberhumer',
              ],
            'e' =>
              [
                0 => 'lzo',
              ],
          ],
        'application/x-lzpdf' =>
          [
            'desc' =>
              [
                0 => 'PDF document (lzip-compressed)',
              ],
            'e' =>
              [
                0 => 'pdf.lz',
              ],
          ],
        'application/x-m4' =>
          [
            'desc' =>
              [
                0 => 'M4 macro',
              ],
            'e' =>
              [
                0 => 'm4',
              ],
          ],
        'application/x-magicpoint' =>
          [
            'desc' =>
              [
                0 => 'MagicPoint presentation',
              ],
            'e' =>
              [
                0 => 'mgp',
              ],
          ],
        'application/x-mame-chd' =>
          [
            'desc' =>
              [
                0 => 'MAME compressed hard disk image',
              ],
            'e' =>
              [
                0 => 'chd',
              ],
          ],
        'application/x-markaby' =>
          [
            'desc' =>
              [
                0 => 'Markaby script',
              ],
            'e' =>
              [
                0 => 'mab',
              ],
          ],
        'application/x-mie' =>
          [
            'e' =>
              [
                0 => 'mie',
              ],
          ],
        'application/x-mif' =>
          [
            'desc' =>
              [
                0 => 'Adobe FrameMaker MIF document',
              ],
            'e' =>
              [
                0 => 'mif',
              ],
          ],
        'application/x-mimearchive' =>
          [
            'desc' =>
              [
                0 => 'MHTML web archive',
                1 => 'MHTML: MIME HTML',
              ],
            'e' =>
              [
                0 => 'mhtml',
                1 => 'mht',
              ],
          ],
        'application/x-mobipocket-ebook' =>
          [
            'desc' =>
              [
                0 => 'Mobipocket e-book',
              ],
            'e' =>
              [
                0 => 'prc',
                1 => 'mobi',
              ],
          ],
        'application/x-modrinth-modpack+zip' =>
          [
            'desc' =>
              [
                0 => 'Modrinth Modpack',
              ],
            'e' =>
              [
                0 => 'mrpack',
              ],
          ],
        'application/x-ms-application' =>
          [
            'e' =>
              [
                0 => 'application',
              ],
          ],
        'application/x-ms-ne-executable' =>
          [
            'desc' =>
              [
                0 => '16-bit Windows program',
              ],
            'e' =>
              [
                0 => 'exe',
                1 => 'dll',
                2 => 'cpl',
                3 => 'drv',
                4 => 'scr',
              ],
          ],
        'application/x-ms-pdb' =>
          [
            'desc' =>
              [
                0 => 'Windows program database',
              ],
            'e' =>
              [
                0 => 'pdb',
              ],
          ],
        'application/x-ms-shortcut' =>
          [
            'a' =>
              [
                0 => 'application/x-win-lnk',
              ],
            'desc' =>
              [
                0 => 'Windows shortcut',
              ],
            'e' =>
              [
                0 => 'lnk',
              ],
          ],
        'application/x-ms-wim' =>
          [
            'desc' =>
              [
                0 => 'WIM disk image',
                1 => 'WIM: Windows Imaging Format',
              ],
            'e' =>
              [
                0 => 'wim',
                1 => 'swm',
              ],
          ],
        'application/x-ms-wmd' =>
          [
            'e' =>
              [
                0 => 'wmd',
              ],
          ],
        'application/x-ms-wmz' =>
          [
            'e' =>
              [
                0 => 'wmz',
              ],
          ],
        'application/x-ms-xbap' =>
          [
            'e' =>
              [
                0 => 'xbap',
              ],
          ],
        'application/x-msbinder' =>
          [
            'e' =>
              [
                0 => 'obd',
              ],
          ],
        'application/x-mscardfile' =>
          [
            'e' =>
              [
                0 => 'crd',
              ],
          ],
        'application/x-msclip' =>
          [
            'e' =>
              [
                0 => 'clp',
              ],
          ],
        'application/x-msdownload' =>
          [
            'a' =>
              [
                0 => 'application/x-ms-dos-executable',
              ],
            'desc' =>
              [
                0 => 'Windows or DOS program',
                1 => 'DOS: Disk Operating System',
              ],
            'e' =>
              [
                0 => 'exe',
                1 => 'dll',
                2 => 'com',
                3 => 'bat',
                4 => 'msi',
                5 => 'cpl',
                6 => 'drv',
                7 => 'scr',
              ],
          ],
        'application/x-msi' =>
          [
            'desc' =>
              [
                0 => 'Windows Installer package',
              ],
            'e' =>
              [
                0 => 'msi',
              ],
          ],
        'application/x-msmediaview' =>
          [
            'e' =>
              [
                0 => 'mvb',
                1 => 'm13',
                2 => 'm14',
              ],
          ],
        'application/x-msmoney' =>
          [
            'e' =>
              [
                0 => 'mny',
              ],
          ],
        'application/x-mspublisher' =>
          [
            'e' =>
              [
                0 => 'pub',
              ],
          ],
        'application/x-msschedule' =>
          [
            'e' =>
              [
                0 => 'scd',
              ],
          ],
        'application/x-msterminal' =>
          [
            'e' =>
              [
                0 => 'trm',
              ],
          ],
        'application/x-mswinurl' =>
          [
            'desc' =>
              [
                0 => 'Internet shortcut',
              ],
            'e' =>
              [
                0 => 'url',
              ],
          ],
        'application/x-mswrite' =>
          [
            'desc' =>
              [
                0 => 'WRI document',
              ],
            'e' =>
              [
                0 => 'wri',
              ],
          ],
        'application/x-msx-rom' =>
          [
            'desc' =>
              [
                0 => 'MSX ROM',
              ],
            'e' =>
              [
                0 => 'msx',
              ],
          ],
        'application/x-n64-rom' =>
          [
            'desc' =>
              [
                0 => 'Nintendo64 ROM',
              ],
            'e' =>
              [
                0 => 'n64',
                1 => 'z64',
                2 => 'v64',
              ],
          ],
        'application/x-navi-animation' =>
          [
            'desc' =>
              [
                0 => 'Windows animated cursor',
              ],
            'e' =>
              [
                0 => 'ani',
              ],
          ],
        'application/x-neo-geo-pocket-color-rom' =>
          [
            'desc' =>
              [
                0 => 'Neo-Geo Pocket Color ROM',
              ],
            'e' =>
              [
                0 => 'ngc',
              ],
          ],
        'application/x-neo-geo-pocket-rom' =>
          [
            'desc' =>
              [
                0 => 'Neo-Geo Pocket ROM',
              ],
            'e' =>
              [
                0 => 'ngp',
              ],
          ],
        'application/x-nes-rom' =>
          [
            'desc' =>
              [
                0 => 'NES ROM',
              ],
            'e' =>
              [
                0 => 'nes',
                1 => 'nez',
                2 => 'unf',
                3 => 'unif',
              ],
          ],
        'application/x-netcdf' =>
          [
            'desc' =>
              [
                0 => 'Unidata NetCDF document',
                1 => 'NetCDF: Network Common Data Form',
              ],
            'e' =>
              [
                0 => 'nc',
                1 => 'cdf',
              ],
          ],
        'application/x-netshow-channel' =>
          [
            'desc' =>
              [
                0 => 'Windows Media Station file',
              ],
            'e' =>
              [
                0 => 'nsc',
              ],
          ],
        'application/x-nintendo-3ds-executable' =>
          [
            'desc' =>
              [
                0 => 'Nintendo 3DS Executable',
              ],
            'e' =>
              [
                0 => '3dsx',
              ],
          ],
        'application/x-nintendo-3ds-rom' =>
          [
            'desc' =>
              [
                0 => 'Nintendo 3DS ROM',
              ],
            'e' =>
              [
                0 => '3ds',
                1 => 'cci',
              ],
          ],
        'application/x-nintendo-ds-rom' =>
          [
            'desc' =>
              [
                0 => 'Nintendo DS ROM',
              ],
            'e' =>
              [
                0 => 'nds',
              ],
          ],
        'application/x-nintendo-switch-xci' =>
          [
            'a' =>
              [
                0 => 'application/x-nx-xci',
              ],
            'desc' =>
              [
                0 => 'Nintendo Switch encrypted ROM',
              ],
            'e' =>
              [
                0 => 'xci',
              ],
          ],
        'application/x-nuscript' =>
          [
            'a' =>
              [
                0 => 'text/x-nu',
              ],
            'desc' =>
              [
                0 => 'Nu shell script',
              ],
            'e' =>
              [
                0 => 'nu',
              ],
          ],
        'application/x-nzb' =>
          [
            'desc' =>
              [
                0 => 'NewzBin usenet index',
              ],
            'e' =>
              [
                0 => 'nzb',
              ],
          ],
        'application/x-object' =>
          [
            'desc' =>
              [
                0 => 'Object code',
              ],
            'e' =>
              [
                0 => 'o',
                1 => 'mod',
              ],
          ],
        'application/x-oleo' =>
          [
            'desc' =>
              [
                0 => 'GNU Oleo spreadsheet',
              ],
            'e' =>
              [
                0 => 'oleo',
              ],
          ],
        'application/x-openvpn-profile' =>
          [
            'desc' =>
              [
                0 => 'OpenVPN profile',
              ],
            'e' =>
              [
                0 => 'openvpn',
                1 => 'ovpn',
              ],
          ],
        'application/x-openzim' =>
          [
            'desc' =>
              [
                0 => 'OpenZIM file',
                1 => 'ZIM: Zeno IMproved',
              ],
            'e' =>
              [
                0 => 'zim',
              ],
          ],
        'application/x-pagemaker' =>
          [
            'desc' =>
              [
                0 => 'Adobe PageMaker document',
              ],
            'e' =>
              [
                0 => 'p65',
                1 => 'pm',
                2 => 'pm6',
                3 => 'pmd',
              ],
          ],
        'application/x-pak' =>
          [
            'desc' =>
              [
                0 => 'PAK archive',
              ],
            'e' =>
              [
                0 => 'pak',
              ],
          ],
        'application/x-par2' =>
          [
            'desc' =>
              [
                0 => 'Parchive archive',
                1 => 'Parchive: Parity Volume Set Archive',
              ],
            'e' =>
              [
                0 => 'par2',
              ],
          ],
        'application/x-partial-download' =>
          [
            'desc' =>
              [
                0 => 'Partially downloaded file',
              ],
            'e' =>
              [
                0 => 'wkdownload',
                1 => 'crdownload',
                2 => 'part',
              ],
          ],
        'application/x-pc-engine-rom' =>
          [
            'desc' =>
              [
                0 => 'PC Engine ROM',
              ],
            'e' =>
              [
                0 => 'pce',
              ],
          ],
        'application/x-pcapng' =>
          [
            'desc' =>
              [
                0 => 'PCAPNG packet capture',
                1 => 'PCAPNG: PCAP Next Generation',
              ],
            'e' =>
              [
                0 => 'pcapng',
                1 => 'ntar',
              ],
          ],
        'application/x-perl' =>
          [
            'a' =>
              [
                0 => 'text/x-perl',
              ],
            'desc' =>
              [
                0 => 'Perl script',
              ],
            'e' =>
              [
                0 => 'pl',
                1 => 'pm',
                2 => 'al',
                3 => 'perl',
                4 => 'pod',
                5 => 't',
              ],
          ],
        'application/x-php' =>
          [
            'desc' =>
              [
                0 => 'PHP script',
              ],
            'e' =>
              [
                0 => 'php',
                1 => 'php3',
                2 => 'php4',
                3 => 'php5',
                4 => 'phps',
              ],
          ],
        'application/x-pkcs7-certificates' =>
          [
            'desc' =>
              [
                0 => 'PKCS#7 certificate bundle',
                1 => 'PKCS: Public-Key Cryptography Standards',
              ],
            'e' =>
              [
                0 => 'p7b',
                1 => 'spc',
              ],
          ],
        'application/x-pkcs7-certreqresp' =>
          [
            'e' =>
              [
                0 => 'p7r',
              ],
          ],
        'application/x-planperfect' =>
          [
            'desc' =>
              [
                0 => 'PlanPerfect spreadsheet',
              ],
            'e' =>
              [
                0 => 'pln',
              ],
          ],
        'application/x-pocket-word' =>
          [
            'desc' =>
              [
                0 => 'Pocket Word document',
              ],
            'e' =>
              [
                0 => 'psw',
              ],
          ],
        'application/x-powershell' =>
          [
            'desc' =>
              [
                0 => 'PowerShell script',
              ],
            'e' =>
              [
                0 => 'ps1',
              ],
          ],
        'application/x-pw' =>
          [
            'desc' =>
              [
                0 => 'Pathetic Writer document',
              ],
            'e' =>
              [
                0 => 'pw',
              ],
          ],
        'application/x-pyspread-bz-spreadsheet' =>
          [
            'desc' =>
              [
                0 => 'Pyspread spreadsheet (bzip2-compressed)',
              ],
            'e' =>
              [
                0 => 'pys',
              ],
          ],
        'application/x-pyspread-spreadsheet' =>
          [
            'desc' =>
              [
                0 => 'Pyspread spreadsheet',
              ],
            'e' =>
              [
                0 => 'pysu',
              ],
          ],
        'application/x-python-bytecode' =>
          [
            'desc' =>
              [
                0 => 'Python bytecode',
              ],
            'e' =>
              [
                0 => 'pyc',
                1 => 'pyo',
              ],
          ],
        'application/x-qbrew' =>
          [
            'desc' =>
              [
                0 => 'QBrew beer recipes',
              ],
            'e' =>
              [
                0 => 'qbrew',
              ],
          ],
        'application/x-qed-disk' =>
          [
            'desc' =>
              [
                0 => 'QEMU QED disk image',
                1 => 'QED: QEMU Enhanced Disk',
              ],
            'e' =>
              [
                0 => 'qed',
              ],
          ],
        'application/x-qemu-disk' =>
          [
            'desc' =>
              [
                0 => 'QEMU QCOW disk image',
                1 => 'QCOW: QEMU Copy On Write',
              ],
            'e' =>
              [
                0 => 'qcow2',
                1 => 'qcow',
              ],
          ],
        'application/x-qpress' =>
          [
            'desc' =>
              [
                0 => 'Qpress archive',
              ],
            'e' =>
              [
                0 => 'qp',
              ],
          ],
        'application/x-qtiplot' =>
          [
            'desc' =>
              [
                0 => 'QtiPlot document',
              ],
            'e' =>
              [
                0 => 'qti',
                1 => 'qti.gz',
              ],
          ],
        'application/x-quattropro' =>
          [
            'desc' =>
              [
                0 => 'Quattro Pro spreadsheet',
              ],
            'e' =>
              [
                0 => 'wb1',
                1 => 'wb2',
                2 => 'wb3',
                3 => 'qpw',
              ],
          ],
        'application/x-quicktime-media-link' =>
          [
            'a' =>
              [
                0 => 'application/x-quicktimeplayer',
              ],
            'desc' =>
              [
                0 => 'QuickTime playlist',
              ],
            'e' =>
              [
                0 => 'qtl',
              ],
          ],
        'application/x-qw' =>
          [
            'desc' =>
              [
                0 => 'Quicken document',
              ],
            'e' =>
              [
                0 => 'qif',
              ],
          ],
        'application/x-raw-disk-image-xz-compressed' =>
          [
            'desc' =>
              [
                0 => 'Raw disk image (XZ-compressed)',
              ],
            'e' =>
              [
                0 => 'raw-disk-image.xz',
                1 => 'img.xz',
              ],
          ],
        'application/x-raw-floppy-disk-image' =>
          [
            'a' =>
              [
                0 => 'application/x-fd-file',
              ],
            'desc' =>
              [
                0 => 'Floppy disk image',
              ],
            'e' =>
              [
                0 => 'fd',
                1 => 'qd',
              ],
          ],
        'application/x-research-info-systems' =>
          [
            'e' =>
              [
                0 => 'ris',
              ],
          ],
        'application/x-rpm' =>
          [
            'a' =>
              [
                0 => 'application/x-redhat-package-manager',
              ],
            'desc' =>
              [
                0 => 'RPM package',
              ],
            'e' =>
              [
                0 => 'rpm',
              ],
          ],
        'application/x-ruby' =>
          [
            'desc' =>
              [
                0 => 'Ruby script',
              ],
            'e' =>
              [
                0 => 'rb',
              ],
          ],
        'application/x-rzip' =>
          [
            'desc' =>
              [
                0 => 'Rzip archive',
              ],
            'e' =>
              [
                0 => 'rz',
              ],
          ],
        'application/x-rzip-compressed-tar' =>
          [
            'desc' =>
              [
                0 => 'Tar archive (rzip-compressed)',
              ],
            'e' =>
              [
                0 => 'tar.rz',
                1 => 'trz',
              ],
          ],
        'application/x-sami' =>
          [
            'desc' =>
              [
                0 => 'SAMI subtitles',
                1 => 'SAMI: Synchronized Accessible Media Interchange',
              ],
            'e' =>
              [
                0 => 'smi',
                1 => 'sami',
              ],
          ],
        'application/x-saturn-rom' =>
          [
            'desc' =>
              [
                0 => 'Sega Saturn disc image',
              ],
            'e' =>
              [
                0 => 'iso',
              ],
          ],
        'application/x-sega-cd-rom' =>
          [
            'desc' =>
              [
                0 => 'Sega CD disc image',
              ],
            'e' =>
              [
                0 => 'iso',
              ],
          ],
        'application/x-sega-pico-rom' =>
          [
            'desc' =>
              [
                0 => 'Sega Pico ROM',
              ],
            'e' =>
              [
                0 => 'iso',
              ],
          ],
        'application/x-sg1000-rom' =>
          [
            'desc' =>
              [
                0 => 'SG-1000 ROM',
              ],
            'e' =>
              [
                0 => 'sg',
              ],
          ],
        'application/x-sh' =>
          [
            'e' =>
              [
                0 => 'sh',
              ],
          ],
        'application/x-shar' =>
          [
            'desc' =>
              [
                0 => 'Shell archive',
              ],
            'e' =>
              [
                0 => 'shar',
              ],
          ],
        'application/x-shared-library-la' =>
          [
            'desc' =>
              [
                0 => 'Libtool shared library',
              ],
            'e' =>
              [
                0 => 'la',
              ],
          ],
        'application/x-sharedlib' =>
          [
            'desc' =>
              [
                0 => 'Shared library',
              ],
            'e' =>
              [
                0 => 'so',
              ],
          ],
        'application/x-shellscript' =>
          [
            'a' =>
              [
                0 => 'text/x-sh',
              ],
            'desc' =>
              [
                0 => 'Shell script',
              ],
            'e' =>
              [
                0 => 'sh',
              ],
          ],
        'application/x-shorten' =>
          [
            'a' =>
              [
                0 => 'audio/x-shorten',
              ],
            'desc' =>
              [
                0 => 'Shorten audio',
              ],
            'e' =>
              [
                0 => 'shn',
              ],
          ],
        'application/x-siag' =>
          [
            'desc' =>
              [
                0 => 'Siag spreadsheet',
              ],
            'e' =>
              [
                0 => 'siag',
              ],
          ],
        'application/x-silverlight-app' =>
          [
            'e' =>
              [
                0 => 'xap',
              ],
          ],
        'application/x-sms-rom' =>
          [
            'desc' =>
              [
                0 => 'Master System ROM',
              ],
            'e' =>
              [
                0 => 'sms',
              ],
          ],
        'application/x-sony-bbeb' =>
          [
            'desc' =>
              [
                0 => 'BroadBand eBook',
              ],
            'e' =>
              [
                0 => 'lrf',
              ],
          ],
        'application/x-source-rpm' =>
          [
            'desc' =>
              [
                0 => 'Source RPM package',
              ],
            'e' =>
              [
                0 => 'src.rpm',
                1 => 'spm',
              ],
          ],
        'application/x-spss-por' =>
          [
            'desc' =>
              [
                0 => 'SPSS portable data file',
                1 => 'SPSS: Statistical Package for the Social Sciences',
              ],
            'e' =>
              [
                0 => 'por',
              ],
          ],
        'application/x-spss-sav' =>
          [
            'a' =>
              [
                0 => 'application/x-spss-savefile',
              ],
            'desc' =>
              [
                0 => 'SPSS data file',
                1 => 'SPSS: Statistical Package for the Social Sciences',
              ],
            'e' =>
              [
                0 => 'sav',
                1 => 'zsav',
              ],
          ],
        'application/x-sql' =>
          [
            'e' =>
              [
                0 => 'sql',
              ],
          ],
        'application/x-sqlite2' =>
          [
            'desc' =>
              [
                0 => 'SQLite2 database',
              ],
            'e' =>
              [
                0 => 'sqlite2',
              ],
          ],
        'application/x-starcalc' =>
          [
            'desc' =>
              [
                0 => 'StarCalc 3-4 spreadsheet',
              ],
            'e' =>
              [
                0 => 'sdc',
              ],
          ],
        'application/x-starchart' =>
          [
            'desc' =>
              [
                0 => 'StarChart 3-4 chart',
              ],
            'e' =>
              [
                0 => 'sds',
              ],
          ],
        'application/x-stardraw' =>
          [
            'desc' =>
              [
                0 => 'StarDraw 4 drawing',
              ],
            'e' =>
              [
                0 => 'sda',
              ],
          ],
        'application/x-starimpress' =>
          [
            'desc' =>
              [
                0 => 'StarImpress 3-4 presentation',
              ],
            'e' =>
              [
                0 => 'sdd',
              ],
          ],
        'application/x-starmail' =>
          [
            'desc' =>
              [
                0 => 'StarMail 4 email',
              ],
            'e' =>
              [
                0 => 'smd',
              ],
          ],
        'application/x-starmath' =>
          [
            'desc' =>
              [
                0 => 'StarMath 3-4 formula',
              ],
            'e' =>
              [
                0 => 'smf',
              ],
          ],
        'application/x-starwriter' =>
          [
            'desc' =>
              [
                0 => 'StarWriter 3-4 document',
              ],
            'e' =>
              [
                0 => 'sdw',
                1 => 'vor',
              ],
          ],
        'application/x-starwriter-global' =>
          [
            'desc' =>
              [
                0 => 'StarWriter 4 master document',
              ],
            'e' =>
              [
                0 => 'sgl',
              ],
          ],
        'application/x-stuffit' =>
          [
            'a' =>
              [
                0 => 'application/stuffit',
                1 => 'application/x-sit',
              ],
            'desc' =>
              [
                0 => 'StuffIt archive',
              ],
            'e' =>
              [
                0 => 'sit',
              ],
          ],
        'application/x-stuffitx' =>
          [
            'a' =>
              [
                0 => 'application/x-sitx',
              ],
            'desc' =>
              [
                0 => 'StuffIt X archive',
              ],
            'e' =>
              [
                0 => 'sitx',
              ],
          ],
        'application/x-subrip' =>
          [
            'a' =>
              [
                0 => 'application/x-srt',
              ],
            'desc' =>
              [
                0 => 'SubRip subtitles',
              ],
            'e' =>
              [
                0 => 'srt',
              ],
          ],
        'application/x-sv4cpio' =>
          [
            'desc' =>
              [
                0 => 'SV4 CPIO archive',
              ],
            'e' =>
              [
                0 => 'sv4cpio',
              ],
          ],
        'application/x-sv4crc' =>
          [
            'desc' =>
              [
                0 => 'SV4 CPIO archive (with CRC)',
              ],
            'e' =>
              [
                0 => 'sv4crc',
              ],
          ],
        'application/x-sylk' =>
          [
            'a' =>
              [
                0 => 'text/spreadsheet',
              ],
            'desc' =>
              [
                0 => 'Spreadsheet interchange document',
              ],
            'e' =>
              [
                0 => 'sylk',
                1 => 'slk',
              ],
          ],
        'application/x-t3vm-image' =>
          [
            'e' =>
              [
                0 => 't3',
              ],
          ],
        'application/x-t602' =>
          [
            'desc' =>
              [
                0 => 'T602 document',
              ],
            'e' =>
              [
                0 => '602',
              ],
          ],
        'application/x-tads' =>
          [
            'e' =>
              [
                0 => 'gam',
              ],
          ],
        'application/x-tar' =>
          [
            'a' =>
              [
                0 => 'application/x-gtar',
              ],
            'desc' =>
              [
                0 => 'Tar archive',
              ],
            'e' =>
              [
                0 => 'tar',
                1 => 'gtar',
                2 => 'gem',
              ],
          ],
        'application/x-tarz' =>
          [
            'desc' =>
              [
                0 => 'Tar archive (compressed)',
              ],
            'e' =>
              [
                0 => 'tar.z',
                1 => 'taz',
              ],
          ],
        'application/x-tcl' =>
          [
            'e' =>
              [
                0 => 'tcl',
              ],
          ],
        'application/x-tex-gf' =>
          [
            'desc' =>
              [
                0 => 'Generic font file',
              ],
            'e' =>
              [
                0 => 'gf',
              ],
          ],
        'application/x-tex-pk' =>
          [
            'desc' =>
              [
                0 => 'Packed font file',
              ],
            'e' =>
              [
                0 => 'pk',
              ],
          ],
        'application/x-tex-tfm' =>
          [
            'e' =>
              [
                0 => 'tfm',
              ],
          ],
        'application/x-texinfo' =>
          [
            'e' =>
              [
                0 => 'texinfo',
                1 => 'texi',
              ],
          ],
        'application/x-tgif' =>
          [
            'desc' =>
              [
                0 => 'TGIF document',
              ],
            'e' =>
              [
                0 => 'obj',
              ],
          ],
        'application/x-theme' =>
          [
            'desc' =>
              [
                0 => 'Theme',
              ],
            'e' =>
              [
                0 => 'theme',
              ],
          ],
        'application/x-thomson-cartridge-memo7' =>
          [
            'desc' =>
              [
                0 => 'Thomson Mémo7 cartridge',
              ],
            'e' =>
              [
                0 => 'm7',
              ],
          ],
        'application/x-thomson-cassette' =>
          [
            'desc' =>
              [
                0 => 'Thomson cassette',
              ],
            'e' =>
              [
                0 => 'k7',
              ],
          ],
        'application/x-thomson-sap-image' =>
          [
            'a' =>
              [
                0 => 'application/x-sap-file',
              ],
            'desc' =>
              [
                0 => 'SAP Thomson floppy disk image',
                1 => 'SAP: Système d\'Archivage Pukall',
              ],
            'e' =>
              [
                0 => 'sap',
              ],
          ],
        'application/x-tiled-tmx' =>
          [
            'desc' =>
              [
                0 => 'Tiled map files',
              ],
            'e' =>
              [
                0 => 'tmx',
              ],
          ],
        'application/x-tiled-tsx' =>
          [
            'desc' =>
              [
                0 => 'Tiled tileset files',
              ],
            'e' =>
              [
                0 => 'tsx',
              ],
          ],
        'application/x-trash' =>
          [
            'desc' =>
              [
                0 => 'Backup file',
              ],
            'e' =>
              [
                0 => 'bak',
                1 => 'old',
                2 => 'sik',
              ],
          ],
        'application/x-troff-man' =>
          [
            'desc' =>
              [
                0 => 'Manual page',
              ],
            'e' =>
              [
                0 => 'man',
              ],
          ],
        'application/x-tzo' =>
          [
            'desc' =>
              [
                0 => 'Tar archive (LZO-compressed)',
              ],
            'e' =>
              [
                0 => 'tar.lzo',
                1 => 'tzo',
              ],
          ],
        'application/x-ufraw' =>
          [
            'desc' =>
              [
                0 => 'UFRaw ID image',
                1 => 'UFRaw: Unidentified Flying Raw',
              ],
            'e' =>
              [
                0 => 'ufraw',
              ],
          ],
        'application/x-ustar' =>
          [
            'desc' =>
              [
                0 => 'Ustar archive',
              ],
            'e' =>
              [
                0 => 'ustar',
              ],
          ],
        'application/x-vdi-disk' =>
          [
            'a' =>
              [
                0 => 'application/x-virtualbox-vdi',
              ],
            'desc' =>
              [
                0 => 'VDI disk image',
                1 => 'VDI: Virtual Disk Image',
              ],
            'e' =>
              [
                0 => 'vdi',
              ],
          ],
        'application/x-vhd-disk' =>
          [
            'a' =>
              [
                0 => 'application/x-virtualbox-vhd',
              ],
            'desc' =>
              [
                0 => 'VHD disk image',
                1 => 'VHD: Virtual Hard Disk',
              ],
            'e' =>
              [
                0 => 'vhd',
                1 => 'vpc',
              ],
          ],
        'application/x-vhdx-disk' =>
          [
            'a' =>
              [
                0 => 'application/x-virtualbox-vhdx',
              ],
            'desc' =>
              [
                0 => 'VHDX disk image',
                1 => 'VHDX: Virtual Hard Disk v2',
              ],
            'e' =>
              [
                0 => 'vhdx',
              ],
          ],
        'application/x-virtual-boy-rom' =>
          [
            'desc' =>
              [
                0 => 'Virtual Boy ROM',
              ],
            'e' =>
              [
                0 => 'vb',
              ],
          ],
        'application/x-vmdk-disk' =>
          [
            'a' =>
              [
                0 => 'application/x-virtualbox-vmdk',
              ],
            'desc' =>
              [
                0 => 'VMDK disk image',
                1 => 'VMDK: Virtual Machine Disk',
              ],
            'e' =>
              [
                0 => 'vmdk',
              ],
          ],
        'application/x-wais-source' =>
          [
            'desc' =>
              [
                0 => 'WAIS source code',
              ],
            'e' =>
              [
                0 => 'src',
              ],
          ],
        'application/x-wii-rom' =>
          [
            'a' =>
              [
                0 => 'application/x-wii-iso-image',
                1 => 'application/x-wbfs',
                2 => 'application/x-wia',
              ],
            'desc' =>
              [
                0 => 'Wii disc image',
              ],
            'e' =>
              [
                0 => 'iso',
              ],
          ],
        'application/x-wii-wad' =>
          [
            'desc' =>
              [
                0 => 'WiiWare bundle',
              ],
            'e' =>
              [
                0 => 'wad',
              ],
          ],
        'application/x-windows-themepack' =>
          [
            'desc' =>
              [
                0 => 'Microsoft Windows theme pack',
              ],
            'e' =>
              [
                0 => 'themepack',
              ],
          ],
        'application/x-wonderswan-color-rom' =>
          [
            'desc' =>
              [
                0 => 'Bandai WonderSwan Color ROM',
              ],
            'e' =>
              [
                0 => 'wsc',
              ],
          ],
        'application/x-wonderswan-rom' =>
          [
            'desc' =>
              [
                0 => 'Bandai WonderSwan ROM',
              ],
            'e' =>
              [
                0 => 'ws',
              ],
          ],
        'application/x-wpg' =>
          [
            'desc' =>
              [
                0 => 'WordPerfect/Drawperfect image',
              ],
            'e' =>
              [
                0 => 'wpg',
              ],
          ],
        'application/x-wwf' =>
          [
            'a' =>
              [
                0 => 'application/wwf',
              ],
            'desc' =>
              [
                0 => 'WWF document',
              ],
            'e' =>
              [
                0 => 'wwf',
              ],
          ],
        'application/x-x509-ca-cert' =>
          [
            'desc' =>
              [
                0 => 'DER/PEM/Netscape-encoded X.509 certificate',
              ],
            'e' =>
              [
                0 => 'der',
                1 => 'crt',
                2 => 'cert',
                3 => 'pem',
              ],
          ],
        'application/x-xar' =>
          [
            'desc' =>
              [
                0 => 'XAR archive',
                1 => 'XAR: eXtensible ARchive',
              ],
            'e' =>
              [
                0 => 'xar',
                1 => 'pkg',
              ],
          ],
        'application/x-xbel' =>
          [
            'desc' =>
              [
                0 => 'XBEL bookmarks',
                1 => 'XBEL: XML Bookmark Exchange Language',
              ],
            'e' =>
              [
                0 => 'xbel',
              ],
          ],
        'application/x-xfig' =>
          [
            'e' =>
              [
                0 => 'fig',
              ],
          ],
        'application/x-xliff+xml' =>
          [
            'e' =>
              [
                0 => 'xlf',
              ],
          ],
        'application/x-xpinstall' =>
          [
            'desc' =>
              [
                0 => 'XPInstall installer module',
              ],
            'e' =>
              [
                0 => 'xpi',
              ],
          ],
        'application/x-xz' =>
          [
            'desc' =>
              [
                0 => 'XZ archive',
              ],
            'e' =>
              [
                0 => 'xz',
              ],
          ],
        'application/x-xz-compressed-tar' =>
          [
            'desc' =>
              [
                0 => 'Tar archive (XZ-compressed)',
              ],
            'e' =>
              [
                0 => 'tar.xz',
                1 => 'txz',
              ],
          ],
        'application/x-xzpdf' =>
          [
            'desc' =>
              [
                0 => 'PDF document (XZ-compressed)',
              ],
            'e' =>
              [
                0 => 'pdf.xz',
              ],
          ],
        'application/x-zip-compressed-fb2' =>
          [
            'desc' =>
              [
                0 => 'Compressed FictionBook document',
              ],
            'e' =>
              [
                0 => 'fb2.zip',
              ],
          ],
        'application/x-zmachine' =>
          [
            'e' =>
              [
                0 => 'z1',
                1 => 'z2',
                2 => 'z3',
                3 => 'z4',
                4 => 'z5',
                5 => 'z6',
                6 => 'z7',
                7 => 'z8',
              ],
          ],
        'application/x-zoo' =>
          [
            'desc' =>
              [
                0 => 'Zoo archive',
              ],
            'e' =>
              [
                0 => 'zoo',
              ],
          ],
        'application/x-zpaq' =>
          [
            'desc' =>
              [
                0 => 'Zpaq Archive',
              ],
            'e' =>
              [
                0 => 'zpaq',
              ],
          ],
        'application/x-zstd-compressed-tar' =>
          [
            'desc' =>
              [
                0 => 'Tar archive (Zstandard-compressed)',
              ],
            'e' =>
              [
                0 => 'tar.zst',
                1 => 'tzst',
              ],
          ],
        'application/xaml+xml' =>
          [
            'e' =>
              [
                0 => 'xaml',
              ],
          ],
        'application/xcap-diff+xml' =>
          [
            'e' =>
              [
                0 => 'xdf',
              ],
          ],
        'application/xenc+xml' =>
          [
            'e' =>
              [
                0 => 'xenc',
              ],
          ],
        'application/xhtml+xml' =>
          [
            'desc' =>
              [
                0 => 'XHTML page',
                1 => 'XHTML: Extensible HyperText Markup Language',
              ],
            'e' =>
              [
                0 => 'xhtml',
                1 => 'xht',
                2 => 'html',
                3 => 'htm',
              ],
          ],
        'application/xliff+xml' =>
          [
            'a' =>
              [
                0 => 'application/x-xliff',
              ],
            'desc' =>
              [
                0 => 'XLIFF translation file',
                1 => 'XLIFF: XML Localization Interchange File Format',
              ],
            'e' =>
              [
                0 => 'xlf',
                1 => 'xliff',
              ],
          ],
        'application/xml' =>
          [
            'a' =>
              [
                0 => 'text/xml',
              ],
            'desc' =>
              [
                0 => 'XML document',
                1 => 'XML: eXtensible Markup Language',
              ],
            'e' =>
              [
                0 => 'xml',
                1 => 'xsl',
                2 => 'xbl',
                3 => 'xsd',
                4 => 'rng',
              ],
          ],
        'application/xml-dtd' =>
          [
            'a' =>
              [
                0 => 'text/x-dtd',
              ],
            'desc' =>
              [
                0 => 'DTD file',
                1 => 'DTD: Document Type Definition',
              ],
            'e' =>
              [
                0 => 'dtd',
              ],
          ],
        'application/xml-external-parsed-entity' =>
          [
            'a' =>
              [
                0 => 'text/xml-external-parsed-entity',
              ],
            'desc' =>
              [
                0 => 'XML entities document',
                1 => 'XML: eXtensible Markup Language',
              ],
            'e' =>
              [
                0 => 'ent',
              ],
          ],
        'application/xop+xml' =>
          [
            'e' =>
              [
                0 => 'xop',
              ],
          ],
        'application/xproc+xml' =>
          [
            'e' =>
              [
                0 => 'xpl',
              ],
          ],
        'application/xslt+xml' =>
          [
            'desc' =>
              [
                0 => 'XSLT stylesheet',
                1 => 'XSLT: eXtensible Stylesheet Language Transformation',
              ],
            'e' =>
              [
                0 => 'xslt',
                1 => 'xsl',
              ],
          ],
        'application/xspf+xml' =>
          [
            'a' =>
              [
                0 => 'application/x-xspf+xml',
              ],
            'desc' =>
              [
                0 => 'XSPF playlist',
                1 => 'XSPF: XML Shareable Playlist Format',
              ],
            'e' =>
              [
                0 => 'xspf',
              ],
          ],
        'application/xv+xml' =>
          [
            'e' =>
              [
                0 => 'mxml',
                1 => 'xhvml',
                2 => 'xvml',
                3 => 'xvm',
              ],
          ],
        'application/yaml' =>
          [
            'a' =>
              [
                0 => 'application/x-yaml',
                1 => 'text/yaml',
                2 => 'text/x-yaml',
              ],
            'desc' =>
              [
                0 => 'YAML document',
                1 => 'YAML: YAML Ain\'t Markup Language',
              ],
            'e' =>
              [
                0 => 'yaml',
                1 => 'yml',
              ],
          ],
        'application/yang' =>
          [
            'e' =>
              [
                0 => 'yang',
              ],
          ],
        'application/yin+xml' =>
          [
            'e' =>
              [
                0 => 'yin',
              ],
          ],
        'application/zip' =>
          [
            'a' =>
              [
                0 => 'application/x-zip-compressed',
                1 => 'application/x-zip',
              ],
            'desc' =>
              [
                0 => 'Zip archive',
              ],
            'e' =>
              [
                0 => 'zip',
                1 => 'zipx',
              ],
          ],
        'application/zlib' =>
          [
            'desc' =>
              [
                0 => 'Zlib archive',
              ],
            'e' =>
              [
                0 => 'zz',
              ],
          ],
        'application/zstd' =>
          [
            'desc' =>
              [
                0 => 'Zstandard archive',
              ],
            'e' =>
              [
                0 => 'zst',
              ],
          ],
        'audio/aac' =>
          [
            'a' =>
              [
                0 => 'audio/x-aac',
              ],
            'desc' =>
              [
                0 => 'AAC audio',
                1 => 'AAC: Advanced Audio Coding',
              ],
            'e' =>
              [
                0 => 'aac',
                1 => 'adts',
                2 => 'ass',
              ],
          ],
        'audio/ac3' =>
          [
            'desc' =>
              [
                0 => 'Dolby Digital audio',
              ],
            'e' =>
              [
                0 => 'ac3',
              ],
          ],
        'audio/adpcm' =>
          [
            'e' =>
              [
                0 => 'adp',
              ],
          ],
        'audio/amr' =>
          [
            'a' =>
              [
                0 => 'audio/amr-encrypted',
              ],
            'desc' =>
              [
                0 => 'AMR audio',
                1 => 'AMR: Adaptive Multi-Rate',
              ],
            'e' =>
              [
                0 => 'amr',
              ],
          ],
        'audio/amr-wb' =>
          [
            'a' =>
              [
                0 => 'audio/amr-wb-encrypted',
              ],
            'desc' =>
              [
                0 => 'AMR-WB audio',
                1 => 'AMR-WB: Adaptive Multi-Rate Wideband',
              ],
            'e' =>
              [
                0 => 'awb',
              ],
          ],
        'audio/annodex' =>
          [
            'a' =>
              [
                0 => 'audio/x-annodex',
              ],
            'desc' =>
              [
                0 => 'Annodex audio',
              ],
            'e' =>
              [
                0 => 'axa',
              ],
          ],
        'audio/basic' =>
          [
            'desc' =>
              [
                0 => 'ULAW (Sun) audio',
              ],
            'e' =>
              [
                0 => 'au',
                1 => 'snd',
              ],
          ],
        'audio/flac' =>
          [
            'a' =>
              [
                0 => 'audio/x-flac',
              ],
            'desc' =>
              [
                0 => 'FLAC audio',
                1 => 'FLAC: Free Lossless Audio Codec',
              ],
            'e' =>
              [
                0 => 'flac',
              ],
          ],
        'audio/midi' =>
          [
            'a' =>
              [
                0 => 'audio/x-midi',
              ],
            'desc' =>
              [
                0 => 'MIDI audio',
                1 => 'MIDI: Musical Instrument Digital Interface',
              ],
            'e' =>
              [
                0 => 'mid',
                1 => 'midi',
                2 => 'kar',
                3 => 'rmi',
              ],
          ],
        'audio/mobile-xmf' =>
          [
            'a' =>
              [
                0 => 'audio/vnd.nokia.mobile-xmf',
              ],
            'desc' =>
              [
                0 => 'Mobile XMF audio',
                1 => 'XMF: eXtensible Music Format',
              ],
            'e' =>
              [
                0 => 'mxmf',
              ],
          ],
        'audio/mp2' =>
          [
            'a' =>
              [
                0 => 'audio/x-mp2',
              ],
            'desc' =>
              [
                0 => 'MP2 audio',
              ],
            'e' =>
              [
                0 => 'mp2',
              ],
          ],
        'audio/mp4' =>
          [
            'a' =>
              [
                0 => 'audio/x-m4a',
                1 => 'audio/m4a',
              ],
            'desc' =>
              [
                0 => 'MPEG-4 audio',
              ],
            'e' =>
              [
                0 => 'm4a',
                1 => 'mp4a',
                2 => 'f4a',
              ],
          ],
        'audio/mpeg' =>
          [
            'a' =>
              [
                0 => 'audio/x-mp3',
                1 => 'audio/x-mpg',
                2 => 'audio/x-mpeg',
                3 => 'audio/mp3',
              ],
            'desc' =>
              [
                0 => 'MP3 audio',
              ],
            'e' =>
              [
                0 => 'mpga',
                1 => 'mp2',
                2 => 'mp2a',
                3 => 'mp3',
                4 => 'm2a',
                5 => 'm3a',
              ],
          ],
        'audio/ogg' =>
          [
            'a' =>
              [
                0 => 'audio/x-ogg',
              ],
            'desc' =>
              [
                0 => 'Ogg audio',
              ],
            'e' =>
              [
                0 => 'oga',
                1 => 'ogg',
                2 => 'spx',
                3 => 'opus',
              ],
          ],
        'audio/prs.sid' =>
          [
            'desc' =>
              [
                0 => 'Commodore 64 audio',
              ],
            'e' =>
              [
                0 => 'sid',
                1 => 'psid',
              ],
          ],
        'audio/s3m' =>
          [
            'e' =>
              [
                0 => 's3m',
              ],
          ],
        'audio/silk' =>
          [
            'e' =>
              [
                0 => 'sil',
              ],
          ],
        'audio/usac' =>
          [
            'desc' =>
              [
                0 => 'USAC audio',
                1 => 'USAC: Unified Speech and Audio Coding',
              ],
            'e' =>
              [
                0 => 'loas',
                1 => 'xhe',
              ],
          ],
        'audio/vnd.audible.aax' =>
          [
            'desc' =>
              [
                0 => 'Audible Enhanced audio',
              ],
            'e' =>
              [
                0 => 'aax',
              ],
          ],
        'audio/vnd.audible.aaxc' =>
          [
            'desc' =>
              [
                0 => 'Audible Enhanced audio',
              ],
            'e' =>
              [
                0 => 'aaxc',
              ],
          ],
        'audio/vnd.dece.audio' =>
          [
            'e' =>
              [
                0 => 'uva',
                1 => 'uvva',
              ],
          ],
        'audio/vnd.digital-winds' =>
          [
            'e' =>
              [
                0 => 'eol',
              ],
          ],
        'audio/vnd.dra' =>
          [
            'e' =>
              [
                0 => 'dra',
              ],
          ],
        'audio/vnd.dts' =>
          [
            'a' =>
              [
                0 => 'audio/x-dts',
              ],
            'desc' =>
              [
                0 => 'DTS audio',
                1 => 'DTS: Digital Theater Systems',
              ],
            'e' =>
              [
                0 => 'dts',
              ],
          ],
        'audio/vnd.dts.hd' =>
          [
            'a' =>
              [
                0 => 'audio/x-dtshd',
              ],
            'desc' =>
              [
                0 => 'DTS-HD audio',
                1 => 'DTS-HD: Digital Theater Systems High Definition',
              ],
            'e' =>
              [
                0 => 'dtshd',
              ],
          ],
        'audio/vnd.lucent.voice' =>
          [
            'e' =>
              [
                0 => 'lvp',
              ],
          ],
        'audio/vnd.ms-playready.media.pya' =>
          [
            'e' =>
              [
                0 => 'pya',
              ],
          ],
        'audio/vnd.nuera.ecelp4800' =>
          [
            'e' =>
              [
                0 => 'ecelp4800',
              ],
          ],
        'audio/vnd.nuera.ecelp7470' =>
          [
            'e' =>
              [
                0 => 'ecelp7470',
              ],
          ],
        'audio/vnd.nuera.ecelp9600' =>
          [
            'e' =>
              [
                0 => 'ecelp9600',
              ],
          ],
        'audio/vnd.rip' =>
          [
            'e' =>
              [
                0 => 'rip',
              ],
          ],
        'audio/vnd.rn-realaudio' =>
          [
            'a' =>
              [
                0 => 'audio/x-pn-realaudio',
                1 => 'audio/vnd.m-realaudio',
              ],
            'desc' =>
              [
                0 => 'RealAudio document',
              ],
            'e' =>
              [
                0 => 'ra',
                1 => 'rax',
              ],
          ],
        'audio/vnd.wave' =>
          [
            'a' =>
              [
                0 => 'audio/wav',
                1 => 'audio/x-wav',
              ],
            'desc' =>
              [
                0 => 'WAV audio',
              ],
            'e' =>
              [
                0 => 'wav',
              ],
          ],
        'audio/webm' =>
          [
            'e' =>
              [
                0 => 'weba',
              ],
          ],
        'audio/x-aifc' =>
          [
            'a' =>
              [
                0 => 'audio/x-aiffc',
              ],
            'desc' =>
              [
                0 => 'AIFC audio',
                1 => 'AIFC: Audio Interchange File format Compressed',
              ],
            'e' =>
              [
                0 => 'aifc',
                1 => 'aiffc',
              ],
          ],
        'audio/x-aiff' =>
          [
            'desc' =>
              [
                0 => 'AIFF/Amiga/Mac audio',
                1 => 'AIFF: Audio Interchange File Format',
              ],
            'e' =>
              [
                0 => 'aif',
                1 => 'aiff',
                2 => 'aifc',
              ],
          ],
        'audio/x-amzxml' =>
          [
            'desc' =>
              [
                0 => 'AmazonMP3 download file',
              ],
            'e' =>
              [
                0 => 'amz',
              ],
          ],
        'audio/x-ape' =>
          [
            'desc' =>
              [
                0 => 'Monkey\'s audio',
              ],
            'e' =>
              [
                0 => 'ape',
              ],
          ],
        'audio/x-caf' =>
          [
            'e' =>
              [
                0 => 'caf',
              ],
          ],
        'audio/x-dff' =>
          [
            'a' =>
              [
                0 => 'audio/dff',
              ],
            'desc' =>
              [
                0 => 'DSDIFF audio',
                1 => 'DSDIFF: Direct Stream Digital Interchange File Format',
              ],
            'e' =>
              [
                0 => 'dff',
              ],
          ],
        'audio/x-dsf' =>
          [
            'a' =>
              [
                0 => 'audio/dsf',
                1 => 'audio/x-dsd',
                2 => 'audio/dsd',
              ],
            'desc' =>
              [
                0 => 'DSF audio',
                1 => 'DSF: Direct stream digital Stream File',
              ],
            'e' =>
              [
                0 => 'dsf',
              ],
          ],
        'audio/x-flac+ogg' =>
          [
            'a' =>
              [
                0 => 'audio/x-oggflac',
              ],
            'desc' =>
              [
                0 => 'Ogg FLAC audio',
              ],
            'e' =>
              [
                0 => 'oga',
                1 => 'ogg',
              ],
          ],
        'audio/x-gsm' =>
          [
            'desc' =>
              [
                0 => 'GSM 06.10 audio',
                1 => 'GSM: Global System for Mobile communications',
              ],
            'e' =>
              [
                0 => 'gsm',
              ],
          ],
        'audio/x-iriver-pla' =>
          [
            'desc' =>
              [
                0 => 'iRiver playlist',
              ],
            'e' =>
              [
                0 => 'pla',
              ],
          ],
        'audio/x-it' =>
          [
            'desc' =>
              [
                0 => 'Impulse Tracker audio',
              ],
            'e' =>
              [
                0 => 'it',
              ],
          ],
        'audio/x-m4b' =>
          [
            'desc' =>
              [
                0 => 'MPEG-4 audio book',
              ],
            'e' =>
              [
                0 => 'm4b',
                1 => 'f4b',
              ],
          ],
        'audio/x-m4r' =>
          [
            'desc' =>
              [
                0 => 'MPEG-4 ringtone',
              ],
            'e' =>
              [
                0 => 'm4r',
              ],
          ],
        'audio/x-matroska' =>
          [
            'desc' =>
              [
                0 => 'Matroska audio',
              ],
            'e' =>
              [
                0 => 'mka',
              ],
          ],
        'audio/x-minipsf' =>
          [
            'desc' =>
              [
                0 => 'MiniPSF audio',
                1 => 'MiniPSF: Miniature Portable Sound Format',
              ],
            'e' =>
              [
                0 => 'minipsf',
              ],
          ],
        'audio/x-mo3' =>
          [
            'desc' =>
              [
                0 => 'Compressed Tracker audio',
              ],
            'e' =>
              [
                0 => 'mo3',
              ],
          ],
        'audio/x-mod' =>
          [
            'desc' =>
              [
                0 => 'Amiga SoundTracker audio',
              ],
            'e' =>
              [
                0 => 'mod',
                1 => 'ult',
                2 => 'uni',
                3 => 'm15',
                4 => 'mtm',
                5 => '669',
                6 => 'med',
              ],
          ],
        'audio/x-mpegurl' =>
          [
            'a' =>
              [
                0 => 'audio/mpegurl',
                1 => 'application/m3u',
                2 => 'audio/x-mp3-playlist',
                3 => 'audio/m3u',
                4 => 'audio/x-m3u',
              ],
            'desc' =>
              [
                0 => 'Media playlist',
              ],
            'e' =>
              [
                0 => 'm3u',
                1 => 'm3u8',
                2 => 'vlc',
              ],
          ],
        'audio/x-ms-asx' =>
          [
            'a' =>
              [
                0 => 'video/x-ms-wvx',
                1 => 'video/x-ms-wax',
                2 => 'video/x-ms-wmx',
                3 => 'application/x-ms-asx',
              ],
            'desc' =>
              [
                0 => 'Microsoft ASX playlist',
              ],
            'e' =>
              [
                0 => 'asx',
                1 => 'wax',
                2 => 'wvx',
                3 => 'wmx',
              ],
          ],
        'audio/x-ms-wax' =>
          [
            'e' =>
              [
                0 => 'wax',
              ],
          ],
        'audio/x-ms-wma' =>
          [
            'a' =>
              [
                0 => 'audio/wma',
              ],
            'desc' =>
              [
                0 => 'Windows Media audio',
              ],
            'e' =>
              [
                0 => 'wma',
              ],
          ],
        'audio/x-musepack' =>
          [
            'desc' =>
              [
                0 => 'Musepack audio',
              ],
            'e' =>
              [
                0 => 'mpc',
                1 => 'mpp',
                2 => 'mp+',
              ],
          ],
        'audio/x-opus+ogg' =>
          [
            'desc' =>
              [
                0 => 'Opus audio',
              ],
            'e' =>
              [
                0 => 'opus',
              ],
          ],
        'audio/x-pn-audibleaudio' =>
          [
            'a' =>
              [
                0 => 'audio/vnd.audible',
              ],
            'desc' =>
              [
                0 => 'Audible.Com audio',
              ],
            'e' =>
              [
                0 => 'aa',
              ],
          ],
        'audio/x-pn-realaudio-plugin' =>
          [
            'e' =>
              [
                0 => 'rmp',
              ],
          ],
        'audio/x-psf' =>
          [
            'desc' =>
              [
                0 => 'PSF audio',
                1 => 'PSF: Portable Sound Format',
              ],
            'e' =>
              [
                0 => 'psf',
              ],
          ],
        'audio/x-psflib' =>
          [
            'desc' =>
              [
                0 => 'PSFlib audio library',
                1 => 'PSFlib: Portable Sound Format Library',
              ],
            'e' =>
              [
                0 => 'psflib',
              ],
          ],
        'audio/x-s3m' =>
          [
            'desc' =>
              [
                0 => 'Scream Tracker 3 audio',
              ],
            'e' =>
              [
                0 => 's3m',
              ],
          ],
        'audio/x-scpls' =>
          [
            'a' =>
              [
                0 => 'application/pls',
                1 => 'audio/scpls',
              ],
            'desc' =>
              [
                0 => 'MP3 ShoutCast playlist',
              ],
            'e' =>
              [
                0 => 'pls',
              ],
          ],
        'audio/x-speex' =>
          [
            'desc' =>
              [
                0 => 'Speex audio',
              ],
            'e' =>
              [
                0 => 'spx',
              ],
          ],
        'audio/x-speex+ogg' =>
          [
            'desc' =>
              [
                0 => 'Ogg Speex audio',
              ],
            'e' =>
              [
                0 => 'oga',
                1 => 'ogg',
                2 => 'spx',
              ],
          ],
        'audio/x-stm' =>
          [
            'desc' =>
              [
                0 => 'Scream Tracker audio',
              ],
            'e' =>
              [
                0 => 'stm',
              ],
          ],
        'audio/x-tak' =>
          [
            'desc' =>
              [
                0 => 'TAK audio',
                1 => 'TAK: Tom\'s lossless Audio Kompressor',
              ],
            'e' =>
              [
                0 => 'tak',
              ],
          ],
        'audio/x-tta' =>
          [
            'a' =>
              [
                0 => 'audio/tta',
              ],
            'desc' =>
              [
                0 => 'TrueAudio audio',
              ],
            'e' =>
              [
                0 => 'tta',
              ],
          ],
        'audio/x-voc' =>
          [
            'desc' =>
              [
                0 => 'VOC audio',
              ],
            'e' =>
              [
                0 => 'voc',
              ],
          ],
        'audio/x-vorbis+ogg' =>
          [
            'a' =>
              [
                0 => 'audio/vorbis',
                1 => 'audio/x-vorbis',
              ],
            'desc' =>
              [
                0 => 'Ogg Vorbis audio',
              ],
            'e' =>
              [
                0 => 'oga',
                1 => 'ogg',
              ],
          ],
        'audio/x-wavpack' =>
          [
            'desc' =>
              [
                0 => 'WavPack audio',
              ],
            'e' =>
              [
                0 => 'wv',
                1 => 'wvp',
              ],
          ],
        'audio/x-wavpack-correction' =>
          [
            'desc' =>
              [
                0 => 'WavPack audio correction file',
              ],
            'e' =>
              [
                0 => 'wvc',
              ],
          ],
        'audio/x-xi' =>
          [
            'desc' =>
              [
                0 => 'FastTracker II instrument',
              ],
            'e' =>
              [
                0 => 'xi',
              ],
          ],
        'audio/x-xm' =>
          [
            'desc' =>
              [
                0 => 'FastTracker II audio',
              ],
            'e' =>
              [
                0 => 'xm',
              ],
          ],
        'audio/x-xmf' =>
          [
            'a' =>
              [
                0 => 'audio/xmf',
              ],
            'desc' =>
              [
                0 => 'XMF audio',
                1 => 'XMF: eXtensible Music Format',
              ],
            'e' =>
              [
                0 => 'xmf',
              ],
          ],
        'audio/xm' =>
          [
            'e' =>
              [
                0 => 'xm',
              ],
          ],
        'chemical/x-cdx' =>
          [
            'e' =>
              [
                0 => 'cdx',
              ],
          ],
        'chemical/x-cif' =>
          [
            'e' =>
              [
                0 => 'cif',
              ],
          ],
        'chemical/x-cmdf' =>
          [
            'e' =>
              [
                0 => 'cmdf',
              ],
          ],
        'chemical/x-cml' =>
          [
            'e' =>
              [
                0 => 'cml',
              ],
          ],
        'chemical/x-csml' =>
          [
            'e' =>
              [
                0 => 'csml',
              ],
          ],
        'chemical/x-pdb' =>
          [
            'desc' =>
              [
                0 => 'Protein Data Bank file',
              ],
            'e' =>
              [
                0 => 'pdb',
                1 => 'brk',
              ],
          ],
        'chemical/x-xyz' =>
          [
            'e' =>
              [
                0 => 'xyz',
              ],
          ],
        'font/collection' =>
          [
            'desc' =>
              [
                0 => 'Font collection',
              ],
            'e' =>
              [
                0 => 'ttc',
              ],
          ],
        'font/otf' =>
          [
            'a' =>
              [
                0 => 'application/x-font-otf',
              ],
            'desc' =>
              [
                0 => 'OpenType font',
              ],
            'e' =>
              [
                0 => 'otf',
              ],
          ],
        'font/ttf' =>
          [
            'a' =>
              [
                0 => 'application/x-font-ttf',
              ],
            'desc' =>
              [
                0 => 'TrueType font',
              ],
            'e' =>
              [
                0 => 'ttf',
              ],
          ],
        'font/woff' =>
          [
            'a' =>
              [
                0 => 'application/font-woff',
              ],
            'desc' =>
              [
                0 => 'WOFF font',
                1 => 'WOFF: Web Open Font Format',
              ],
            'e' =>
              [
                0 => 'woff',
              ],
          ],
        'font/woff2' =>
          [
            'desc' =>
              [
                0 => 'WOFF2 font',
                1 => 'WOFF2: Web Open Font Format 2.0',
              ],
            'e' =>
              [
                0 => 'woff2',
              ],
          ],
        'image/apng' =>
          [
            'a' =>
              [
                0 => 'image/vnd.mozilla.apng',
              ],
            'desc' =>
              [
                0 => 'Animated PNG image',
                1 => 'PNG: Portable Network Graphics',
              ],
            'e' =>
              [
                0 => 'apng',
                1 => 'png',
              ],
          ],
        'image/astc' =>
          [
            'desc' =>
              [
                0 => 'ASTC texture',
                1 => 'ASTC: Advanced Scalable Texture Compression',
              ],
            'e' =>
              [
                0 => 'astc',
              ],
          ],
        'image/avif' =>
          [
            'a' =>
              [
                0 => 'image/avif-sequence',
              ],
            'desc' =>
              [
                0 => 'AVIF image',
                1 => 'AVIF: AV1 Image File Format',
              ],
            'e' =>
              [
                0 => 'avif',
                1 => 'avifs',
              ],
          ],
        'image/bmp' =>
          [
            'a' =>
              [
                0 => 'image/x-bmp',
                1 => 'image/x-ms-bmp',
              ],
            'desc' =>
              [
                0 => 'Windows BMP image',
              ],
            'e' =>
              [
                0 => 'bmp',
                1 => 'dib',
              ],
          ],
        'image/cgm' =>
          [
            'desc' =>
              [
                0 => 'CGM image',
                1 => 'CGM: Computer Graphics Metafile',
              ],
            'e' =>
              [
                0 => 'cgm',
              ],
          ],
        'image/emf' =>
          [
            'a' =>
              [
                0 => 'image/x-emf',
                1 => 'application/x-emf',
                2 => 'application/emf',
              ],
            'desc' =>
              [
                0 => 'EMF image',
                1 => 'EMF: Enhanced MetaFile',
              ],
            'e' =>
              [
                0 => 'emf',
              ],
          ],
        'image/g3fax' =>
          [
            'a' =>
              [
                0 => 'image/fax-g3',
              ],
            'desc' =>
              [
                0 => 'CCITT G3 fax image',
                1 => 'CCITT: Comité Consultatif International Téléphonique et Télégraphique',
              ],
            'e' =>
              [
                0 => 'g3',
              ],
          ],
        'image/gif' =>
          [
            'desc' =>
              [
                0 => 'GIF image',
                1 => 'GIF: Graphics Interchange Format',
              ],
            'e' =>
              [
                0 => 'gif',
              ],
          ],
        'image/heif' =>
          [
            'a' =>
              [
                0 => 'image/heic',
                1 => 'image/heic-sequence',
                2 => 'image/heif-sequence',
              ],
            'desc' =>
              [
                0 => 'HEIF image',
                1 => 'HEIF: High Efficiency Image File',
              ],
            'e' =>
              [
                0 => 'heic',
                1 => 'heif',
                2 => 'hif',
              ],
          ],
        'image/hej2k' =>
          [
            'desc' =>
              [
                0 => 'JPEG 2000 image encapsulated in HEIF',
              ],
            'e' =>
              [
                0 => 'hej2',
              ],
          ],
        'image/ief' =>
          [
            'desc' =>
              [
                0 => 'IEF image',
              ],
            'e' =>
              [
                0 => 'ief',
              ],
          ],
        'image/jp2' =>
          [
            'a' =>
              [
                0 => 'image/jpeg2000',
                1 => 'image/jpeg2000-image',
                2 => 'image/x-jpeg2000-image',
              ],
            'desc' =>
              [
                0 => 'JPEG-2000 JP2 image',
                1 => 'JP2: JPEG-2000',
              ],
            'e' =>
              [
                0 => 'jp2',
                1 => 'jpg2',
              ],
          ],
        'image/jpeg' =>
          [
            'a' =>
              [
                0 => 'image/pjpeg',
              ],
            'desc' =>
              [
                0 => 'JPEG image',
                1 => 'JPEG: Joint Photographic Experts Group',
              ],
            'e' =>
              [
                0 => 'jpeg',
                1 => 'jpg',
                2 => 'jpe',
                3 => 'jfif',
              ],
          ],
        'image/jpm' =>
          [
            'desc' =>
              [
                0 => 'JPEG-2000 JPM image',
                1 => 'JPM: JPEG-2000 Mixed',
              ],
            'e' =>
              [
                0 => 'jpm',
                1 => 'jpgm',
              ],
          ],
        'image/jpx' =>
          [
            'desc' =>
              [
                0 => 'JPEG-2000 JPX image',
                1 => 'JPX: JPEG-2000 eXtended',
              ],
            'e' =>
              [
                0 => 'jpf',
                1 => 'jpx',
              ],
          ],
        'image/jxl' =>
          [
            'desc' =>
              [
                0 => 'JPEG XL image',
              ],
            'e' =>
              [
                0 => 'jxl',
              ],
          ],
        'image/jxr' =>
          [
            'a' =>
              [
                0 => 'image/vnd.ms-photo',
              ],
            'desc' =>
              [
                0 => 'JPEG XR image',
                1 => 'XR: Extended Range',
              ],
            'e' =>
              [
                0 => 'jxr',
                1 => 'hdp',
                2 => 'wdp',
              ],
          ],
        'image/ktx' =>
          [
            'desc' =>
              [
                0 => 'Khronos texture image',
              ],
            'e' =>
              [
                0 => 'ktx',
              ],
          ],
        'image/ktx2' =>
          [
            'desc' =>
              [
                0 => 'Khronos texture image',
              ],
            'e' =>
              [
                0 => 'ktx2',
              ],
          ],
        'image/openraster' =>
          [
            'desc' =>
              [
                0 => 'OpenRaster image',
              ],
            'e' =>
              [
                0 => 'ora',
              ],
          ],
        'image/png' =>
          [
            'desc' =>
              [
                0 => 'PNG image',
                1 => 'PNG: Portable Network Graphics',
              ],
            'e' =>
              [
                0 => 'png',
              ],
          ],
        'image/prs.btif' =>
          [
            'e' =>
              [
                0 => 'btif',
              ],
          ],
        'image/qoi' =>
          [
            'desc' =>
              [
                0 => 'Quite OK Image Format',
              ],
            'e' =>
              [
                0 => 'qoi',
              ],
          ],
        'image/rle' =>
          [
            'desc' =>
              [
                0 => 'RLE bitmap image',
                1 => 'RLE: Run Length Encoded',
              ],
            'e' =>
              [
                0 => 'rle',
              ],
          ],
        'image/sgi' =>
          [
            'e' =>
              [
                0 => 'sgi',
              ],
          ],
        'image/svg+xml' =>
          [
            'desc' =>
              [
                0 => 'SVG image',
                1 => 'SVG: Scalable Vector Graphics',
              ],
            'e' =>
              [
                0 => 'svg',
                1 => 'svgz',
              ],
          ],
        'image/svg+xml-compressed' =>
          [
            'desc' =>
              [
                0 => 'Compressed SVG image',
                1 => 'SVG: Scalable Vector Graphics',
              ],
            'e' =>
              [
                0 => 'svgz',
                1 => 'svg.gz',
              ],
          ],
        'image/tiff' =>
          [
            'desc' =>
              [
                0 => 'TIFF image',
                1 => 'TIFF: Tagged Image File Format',
              ],
            'e' =>
              [
                0 => 'tiff',
                1 => 'tif',
              ],
          ],
        'image/vnd.adobe.photoshop' =>
          [
            'a' =>
              [
                0 => 'image/psd',
                1 => 'image/x-psd',
                2 => 'image/photoshop',
                3 => 'image/x-photoshop',
                4 => 'application/photoshop',
                5 => 'application/x-photoshop',
              ],
            'desc' =>
              [
                0 => 'Photoshop image',
              ],
            'e' =>
              [
                0 => 'psd',
              ],
          ],
        'image/vnd.dece.graphic' =>
          [
            'e' =>
              [
                0 => 'uvi',
                1 => 'uvvi',
                2 => 'uvg',
                3 => 'uvvg',
              ],
          ],
        'image/vnd.djvu' =>
          [
            'a' =>
              [
                0 => 'image/x-djvu',
                1 => 'image/x.djvu',
              ],
            'desc' =>
              [
                0 => 'DjVu image',
              ],
            'e' =>
              [
                0 => 'djvu',
                1 => 'djv',
              ],
          ],
        'image/vnd.djvu+multipage' =>
          [
            'desc' =>
              [
                0 => 'DjVu document',
              ],
            'e' =>
              [
                0 => 'djvu',
                1 => 'djv',
              ],
          ],
        'image/vnd.dvb.subtitle' =>
          [
            'e' =>
              [
                0 => 'sub',
              ],
          ],
        'image/vnd.dwg' =>
          [
            'desc' =>
              [
                0 => 'AutoCAD image',
              ],
            'e' =>
              [
                0 => 'dwg',
              ],
          ],
        'image/vnd.dxf' =>
          [
            'desc' =>
              [
                0 => 'DXF vector image',
              ],
            'e' =>
              [
                0 => 'dxf',
              ],
          ],
        'image/vnd.fastbidsheet' =>
          [
            'e' =>
              [
                0 => 'fbs',
              ],
          ],
        'image/vnd.fpx' =>
          [
            'a' =>
              [
                0 => 'image/x-fpx',
              ],
            'desc' =>
              [
                0 => 'FlashPix image',
              ],
            'e' =>
              [
                0 => 'fpx',
              ],
          ],
        'image/vnd.fst' =>
          [
            'e' =>
              [
                0 => 'fst',
              ],
          ],
        'image/vnd.fujixerox.edmics-mmr' =>
          [
            'e' =>
              [
                0 => 'mmr',
              ],
          ],
        'image/vnd.fujixerox.edmics-rlc' =>
          [
            'e' =>
              [
                0 => 'rlc',
              ],
          ],
        'image/vnd.microsoft.icon' =>
          [
            'a' =>
              [
                0 => 'application/ico',
                1 => 'image/ico',
                2 => 'image/icon',
                3 => 'image/x-ico',
                4 => 'image/x-icon',
                5 => 'text/ico',
              ],
            'desc' =>
              [
                0 => 'Windows icon',
              ],
            'e' =>
              [
                0 => 'ico',
              ],
          ],
        'image/vnd.ms-modi' =>
          [
            'desc' =>
              [
                0 => 'MDI image',
                1 => 'MDI: Microsoft Document Imaging',
              ],
            'e' =>
              [
                0 => 'mdi',
              ],
          ],
        'image/vnd.net-fpx' =>
          [
            'e' =>
              [
                0 => 'npx',
              ],
          ],
        'image/vnd.rn-realpix' =>
          [
            'desc' =>
              [
                0 => 'RealPix document',
              ],
            'e' =>
              [
                0 => 'rp',
              ],
          ],
        'image/vnd.wap.wbmp' =>
          [
            'desc' =>
              [
                0 => 'WBMP image',
                1 => 'WBMP: WAP bitmap',
              ],
            'e' =>
              [
                0 => 'wbmp',
              ],
          ],
        'image/vnd.xiff' =>
          [
            'e' =>
              [
                0 => 'xif',
              ],
          ],
        'image/vnd.zbrush.pcx' =>
          [
            'a' =>
              [
                0 => 'image/x-pcx',
              ],
            'desc' =>
              [
                0 => 'PCX image',
                1 => 'PCX: PiCture eXchange',
              ],
            'e' =>
              [
                0 => 'pcx',
              ],
          ],
        'image/webp' =>
          [
            'desc' =>
              [
                0 => 'WebP image',
              ],
            'e' =>
              [
                0 => 'webp',
              ],
          ],
        'image/wmf' =>
          [
            'a' =>
              [
                0 => 'image/x-wmf',
                1 => 'image/x-win-metafile',
                2 => 'application/x-wmf',
                3 => 'application/wmf',
                4 => 'application/x-msmetafile',
              ],
            'desc' =>
              [
                0 => 'WMF image',
                1 => 'WMF: Windows Metafile',
              ],
            'e' =>
              [
                0 => 'wmf',
                1 => 'emz',
              ],
          ],
        'image/x-3ds' =>
          [
            'desc' =>
              [
                0 => '3D Studio image',
              ],
            'e' =>
              [
                0 => '3ds',
              ],
          ],
        'image/x-adobe-dng' =>
          [
            'desc' =>
              [
                0 => 'Adobe DNG negative',
                1 => 'DNG: Digital Negative',
              ],
            'e' =>
              [
                0 => 'dng',
              ],
          ],
        'image/x-applix-graphics' =>
          [
            'desc' =>
              [
                0 => 'Applix Graphics image',
              ],
            'e' =>
              [
                0 => 'ag',
              ],
          ],
        'image/x-bzeps' =>
          [
            'desc' =>
              [
                0 => 'EPS image (bzip2-compressed)',
              ],
            'e' =>
              [
                0 => 'eps.bz2',
                1 => 'epsi.bz2',
                2 => 'epsf.bz2',
              ],
          ],
        'image/x-canon-cr2' =>
          [
            'desc' =>
              [
                0 => 'Canon CR2 raw image',
                1 => 'CR2: Canon Raw 2',
              ],
            'e' =>
              [
                0 => 'cr2',
              ],
          ],
        'image/x-canon-cr3' =>
          [
            'desc' =>
              [
                0 => 'Canon CR3 raw image',
                1 => 'CR3: Canon Raw 3',
              ],
            'e' =>
              [
                0 => 'cr3',
              ],
          ],
        'image/x-canon-crw' =>
          [
            'desc' =>
              [
                0 => 'Canon CRW raw image',
                1 => 'CRW: Canon RaW',
              ],
            'e' =>
              [
                0 => 'crw',
              ],
          ],
        'image/x-cmu-raster' =>
          [
            'desc' =>
              [
                0 => 'CMU raster image',
              ],
            'e' =>
              [
                0 => 'ras',
              ],
          ],
        'image/x-cmx' =>
          [
            'e' =>
              [
                0 => 'cmx',
              ],
          ],
        'image/x-compressed-xcf' =>
          [
            'desc' =>
              [
                0 => 'Compressed GIMP image',
              ],
            'e' =>
              [
                0 => 'xcf.gz',
                1 => 'xcf.bz2',
              ],
          ],
        'image/x-dds' =>
          [
            'desc' =>
              [
                0 => 'DirectDraw surface',
              ],
            'e' =>
              [
                0 => 'dds',
              ],
          ],
        'image/x-eps' =>
          [
            'desc' =>
              [
                0 => 'EPS image',
                1 => 'EPS: Encapsulated PostScript',
              ],
            'e' =>
              [
                0 => 'eps',
                1 => 'epsi',
                2 => 'epsf',
              ],
          ],
        'image/x-exr' =>
          [
            'desc' =>
              [
                0 => 'EXR image',
              ],
            'e' =>
              [
                0 => 'exr',
              ],
          ],
        'image/x-freehand' =>
          [
            'e' =>
              [
                0 => 'fh',
                1 => 'fhc',
                2 => 'fh4',
                3 => 'fh5',
                4 => 'fh7',
              ],
          ],
        'image/x-fuji-raf' =>
          [
            'desc' =>
              [
                0 => 'Fuji RAF raw image',
                1 => 'RAF: RAw Format',
              ],
            'e' =>
              [
                0 => 'raf',
              ],
          ],
        'image/x-gimp-gbr' =>
          [
            'desc' =>
              [
                0 => 'GIMP brush',
              ],
            'e' =>
              [
                0 => 'gbr',
              ],
          ],
        'image/x-gimp-gih' =>
          [
            'desc' =>
              [
                0 => 'GIMP brush pipe',
              ],
            'e' =>
              [
                0 => 'gih',
              ],
          ],
        'image/x-gimp-pat' =>
          [
            'desc' =>
              [
                0 => 'GIMP pattern',
              ],
            'e' =>
              [
                0 => 'pat',
              ],
          ],
        'image/x-gzeps' =>
          [
            'desc' =>
              [
                0 => 'EPS image (gzip-compressed)',
              ],
            'e' =>
              [
                0 => 'eps.gz',
                1 => 'epsi.gz',
                2 => 'epsf.gz',
              ],
          ],
        'image/x-icns' =>
          [
            'desc' =>
              [
                0 => 'MacOS X icon',
              ],
            'e' =>
              [
                0 => 'icns',
              ],
          ],
        'image/x-ilbm' =>
          [
            'a' =>
              [
                0 => 'image/x-iff',
              ],
            'desc' =>
              [
                0 => 'ILBM image',
                1 => 'ILBM: InterLeaved BitMap',
              ],
            'e' =>
              [
                0 => 'iff',
                1 => 'ilbm',
                2 => 'lbm',
              ],
          ],
        'image/x-jng' =>
          [
            'desc' =>
              [
                0 => 'JNG image',
                1 => 'JNG: JPEG Network Graphics',
              ],
            'e' =>
              [
                0 => 'jng',
              ],
          ],
        'image/x-jp2-codestream' =>
          [
            'desc' =>
              [
                0 => 'JPEG-2000 codestream',
              ],
            'e' =>
              [
                0 => 'j2c',
                1 => 'j2k',
                2 => 'jpc',
              ],
          ],
        'image/x-kiss-cel' =>
          [
            'desc' =>
              [
                0 => 'KiSS cel',
                1 => 'KiSS: Kisekae Set System',
              ],
            'e' =>
              [
                0 => 'cel',
                1 => 'kcf',
              ],
          ],
        'image/x-kodak-dcr' =>
          [
            'desc' =>
              [
                0 => 'Kodak DCR raw image',
                1 => 'DCR: Digital Camera Raw',
              ],
            'e' =>
              [
                0 => 'dcr',
              ],
          ],
        'image/x-kodak-k25' =>
          [
            'desc' =>
              [
                0 => 'Kodak K25 raw image',
                1 => 'K25: Kodak DC25',
              ],
            'e' =>
              [
                0 => 'k25',
              ],
          ],
        'image/x-kodak-kdc' =>
          [
            'desc' =>
              [
                0 => 'Kodak KDC raw image',
                1 => 'KDC: Kodak Digital Camera',
              ],
            'e' =>
              [
                0 => 'kdc',
              ],
          ],
        'image/x-lwo' =>
          [
            'desc' =>
              [
                0 => 'LightWave object',
              ],
            'e' =>
              [
                0 => 'lwo',
                1 => 'lwob',
              ],
          ],
        'image/x-lws' =>
          [
            'desc' =>
              [
                0 => 'LightWave scene',
              ],
            'e' =>
              [
                0 => 'lws',
              ],
          ],
        'image/x-macpaint' =>
          [
            'desc' =>
              [
                0 => 'MacPaint Bitmap image',
              ],
            'e' =>
              [
                0 => 'pntg',
              ],
          ],
        'image/x-minolta-mrw' =>
          [
            'desc' =>
              [
                0 => 'Minolta MRW raw image',
                1 => 'MRW: Minolta RaW',
              ],
            'e' =>
              [
                0 => 'mrw',
              ],
          ],
        'image/x-mrsid-image' =>
          [
            'e' =>
              [
                0 => 'sid',
              ],
          ],
        'image/x-msod' =>
          [
            'desc' =>
              [
                0 => 'Office drawing',
              ],
            'e' =>
              [
                0 => 'msod',
              ],
          ],
        'image/x-nikon-nef' =>
          [
            'desc' =>
              [
                0 => 'Nikon NEF raw image',
                1 => 'NEF: Nikon Electronic Format',
              ],
            'e' =>
              [
                0 => 'nef',
              ],
          ],
        'image/x-nikon-nrw' =>
          [
            'desc' =>
              [
                0 => 'Nikon NRW raw image',
              ],
            'e' =>
              [
                0 => 'nrw',
              ],
          ],
        'image/x-olympus-orf' =>
          [
            'desc' =>
              [
                0 => 'Olympus ORF raw image',
                1 => 'ORF: Olympus Raw Format',
              ],
            'e' =>
              [
                0 => 'orf',
              ],
          ],
        'image/x-panasonic-rw' =>
          [
            'a' =>
              [
                0 => 'image/x-panasonic-raw',
              ],
            'desc' =>
              [
                0 => 'Panasonic raw image',
              ],
            'e' =>
              [
                0 => 'raw',
              ],
          ],
        'image/x-panasonic-rw2' =>
          [
            'a' =>
              [
                0 => 'image/x-panasonic-raw2',
              ],
            'desc' =>
              [
                0 => 'Panasonic raw image',
              ],
            'e' =>
              [
                0 => 'rw2',
              ],
          ],
        'image/x-pentax-pef' =>
          [
            'desc' =>
              [
                0 => 'Pentax PEF raw image',
                1 => 'PEF: Pentax Electronic Format',
              ],
            'e' =>
              [
                0 => 'pef',
              ],
          ],
        'image/x-photo-cd' =>
          [
            'desc' =>
              [
                0 => 'PCD image',
                1 => 'PCD: PhotoCD',
              ],
            'e' =>
              [
                0 => 'pcd',
              ],
          ],
        'image/x-pict' =>
          [
            'desc' =>
              [
                0 => 'Macintosh Quickdraw/PICT drawing',
              ],
            'e' =>
              [
                0 => 'pic',
                1 => 'pct',
                2 => 'pict',
                3 => 'pict1',
                4 => 'pict2',
              ],
          ],
        'image/x-portable-anymap' =>
          [
            'desc' =>
              [
                0 => 'PNM image',
                1 => 'PNM: Portable Anymap',
              ],
            'e' =>
              [
                0 => 'pnm',
              ],
          ],
        'image/x-portable-bitmap' =>
          [
            'desc' =>
              [
                0 => 'PBM image',
                1 => 'PBM: Portable BitMap',
              ],
            'e' =>
              [
                0 => 'pbm',
              ],
          ],
        'image/x-portable-graymap' =>
          [
            'desc' =>
              [
                0 => 'PGM image',
                1 => 'PGM: Portable GrayMap',
              ],
            'e' =>
              [
                0 => 'pgm',
              ],
          ],
        'image/x-portable-pixmap' =>
          [
            'desc' =>
              [
                0 => 'PPM image',
                1 => 'PPM: Portable PixMap',
              ],
            'e' =>
              [
                0 => 'ppm',
              ],
          ],
        'image/x-quicktime' =>
          [
            'desc' =>
              [
                0 => 'QuickTime image',
              ],
            'e' =>
              [
                0 => 'qtif',
                1 => 'qif',
              ],
          ],
        'image/x-rgb' =>
          [
            'desc' =>
              [
                0 => 'RGB image',
              ],
            'e' =>
              [
                0 => 'rgb',
              ],
          ],
        'image/x-sgi' =>
          [
            'desc' =>
              [
                0 => 'SGI image',
              ],
            'e' =>
              [
                0 => 'sgi',
              ],
          ],
        'image/x-sigma-x3f' =>
          [
            'desc' =>
              [
                0 => 'Sigma X3F raw image',
                1 => 'X3F: X3 Foveon',
              ],
            'e' =>
              [
                0 => 'x3f',
              ],
          ],
        'image/x-skencil' =>
          [
            'desc' =>
              [
                0 => 'Skencil document',
              ],
            'e' =>
              [
                0 => 'sk',
                1 => 'sk1',
              ],
          ],
        'image/x-sony-arw' =>
          [
            'desc' =>
              [
                0 => 'Sony ARW raw image',
                1 => 'ARW: Alpha Raw format',
              ],
            'e' =>
              [
                0 => 'arw',
              ],
          ],
        'image/x-sony-sr2' =>
          [
            'desc' =>
              [
                0 => 'Sony SR2 raw image',
                1 => 'SR2: Sony Raw format 2',
              ],
            'e' =>
              [
                0 => 'sr2',
              ],
          ],
        'image/x-sony-srf' =>
          [
            'desc' =>
              [
                0 => 'Sony SRF raw image',
                1 => 'SRF: Sony Raw Format',
              ],
            'e' =>
              [
                0 => 'srf',
              ],
          ],
        'image/x-sun-raster' =>
          [
            'desc' =>
              [
                0 => 'Sun raster image',
              ],
            'e' =>
              [
                0 => 'sun',
              ],
          ],
        'image/x-tga' =>
          [
            'a' =>
              [
                0 => 'application/tga',
                1 => 'application/x-targa',
                2 => 'application/x-tga',
                3 => 'image/targa',
                4 => 'image/tga',
                5 => 'image/x-icb',
                6 => 'image/x-targa',
              ],
            'desc' =>
              [
                0 => 'TGA image',
                1 => 'TGA: Truevision Graphics Adapter',
              ],
            'e' =>
              [
                0 => 'tga',
                1 => 'icb',
                2 => 'tpic',
                3 => 'vda',
                4 => 'vst',
              ],
          ],
        'image/x-win-bitmap' =>
          [
            'desc' =>
              [
                0 => 'Windows cursor',
              ],
            'e' =>
              [
                0 => 'cur',
              ],
          ],
        'image/x-xbitmap' =>
          [
            'desc' =>
              [
                0 => 'XBM image',
                1 => 'XBM: X BitMap',
              ],
            'e' =>
              [
                0 => 'xbm',
              ],
          ],
        'image/x-xcf' =>
          [
            'desc' =>
              [
                0 => 'GIMP image',
              ],
            'e' =>
              [
                0 => 'xcf',
              ],
          ],
        'image/x-xfig' =>
          [
            'desc' =>
              [
                0 => 'XFig image',
              ],
            'e' =>
              [
                0 => 'fig',
              ],
          ],
        'image/x-xpixmap' =>
          [
            'a' =>
              [
                0 => 'image/x-xpm',
              ],
            'desc' =>
              [
                0 => 'XPM image',
                1 => 'XPM: X PixMap',
              ],
            'e' =>
              [
                0 => 'xpm',
              ],
          ],
        'image/x-xwindowdump' =>
          [
            'desc' =>
              [
                0 => 'X window image',
              ],
            'e' =>
              [
                0 => 'xwd',
              ],
          ],
        'message/rfc822' =>
          [
            'desc' =>
              [
                0 => 'Email message',
              ],
            'e' =>
              [
                0 => 'eml',
                1 => 'mime',
              ],
          ],
        'model/3mf' =>
          [
            'a' =>
              [
                0 => 'application/vnd.ms-3mfdocument',
              ],
            'desc' =>
              [
                0 => '3MF document',
                1 => '3MF: 3D Manufacturing Format',
              ],
            'e' =>
              [
                0 => '3mf',
              ],
          ],
        'model/gltf+json' =>
          [
            'desc' =>
              [
                0 => 'glTF model',
                1 => 'glTF: GL Transmission Format',
              ],
            'e' =>
              [
                0 => 'gltf',
              ],
          ],
        'model/gltf-binary' =>
          [
            'desc' =>
              [
                0 => 'glTF model',
                1 => 'glTF: GL Transmission Format',
              ],
            'e' =>
              [
                0 => 'glb',
              ],
          ],
        'model/iges' =>
          [
            'desc' =>
              [
                0 => 'IGES document',
                1 => 'IGES: Initial Graphics Exchange Specification',
              ],
            'e' =>
              [
                0 => 'igs',
                1 => 'iges',
              ],
          ],
        'model/mesh' =>
          [
            'e' =>
              [
                0 => 'msh',
                1 => 'mesh',
                2 => 'silo',
              ],
          ],
        'model/mtl' =>
          [
            'desc' =>
              [
                0 => 'OBJ 3D model material library',
              ],
            'e' =>
              [
                0 => 'mtl',
              ],
          ],
        'model/obj' =>
          [
            'a' =>
              [
                0 => 'application/prs.wavefront-obj',
              ],
            'desc' =>
              [
                0 => 'OBJ 3D model',
              ],
            'e' =>
              [
                0 => 'obj',
              ],
          ],
        'model/step' =>
          [
            'desc' =>
              [
                0 => 'STEP 3D model',
              ],
            'e' =>
              [
                0 => 'step',
                1 => 'stp',
              ],
          ],
        'model/stl' =>
          [
            'a' =>
              [
                0 => 'model/x.stl-ascii',
                1 => 'model/x.stl-binary',
              ],
            'desc' =>
              [
                0 => 'STL 3D model',
                1 => 'STL: StereoLithography',
              ],
            'e' =>
              [
                0 => 'stl',
              ],
          ],
        'model/vnd.collada+xml' =>
          [
            'e' =>
              [
                0 => 'dae',
              ],
          ],
        'model/vnd.dwf' =>
          [
            'e' =>
              [
                0 => 'dwf',
              ],
          ],
        'model/vnd.gdl' =>
          [
            'e' =>
              [
                0 => 'gdl',
              ],
          ],
        'model/vnd.gtw' =>
          [
            'e' =>
              [
                0 => 'gtw',
              ],
          ],
        'model/vnd.vtu' =>
          [
            'e' =>
              [
                0 => 'vtu',
              ],
          ],
        'model/vrml' =>
          [
            'desc' =>
              [
                0 => 'VRML document',
                1 => 'VRML: Virtual Reality Modeling Language',
              ],
            'e' =>
              [
                0 => 'wrl',
                1 => 'vrml',
                2 => 'vrm',
              ],
          ],
        'model/x3d+binary' =>
          [
            'e' =>
              [
                0 => 'x3db',
                1 => 'x3dbz',
              ],
          ],
        'model/x3d+vrml' =>
          [
            'e' =>
              [
                0 => 'x3dv',
                1 => 'x3dvz',
              ],
          ],
        'model/x3d+xml' =>
          [
            'e' =>
              [
                0 => 'x3d',
                1 => 'x3dz',
              ],
          ],
        'text/cache-manifest' =>
          [
            'desc' =>
              [
                0 => 'Web application cache file',
              ],
            'e' =>
              [
                0 => 'appcache',
                1 => 'manifest',
              ],
          ],
        'text/calendar' =>
          [
            'a' =>
              [
                0 => 'text/x-vcalendar',
                1 => 'application/ics',
              ],
            'desc' =>
              [
                0 => 'VCS/ICS calendar',
                1 => 'VCS/ICS: vCalendar/iCalendar',
              ],
            'e' =>
              [
                0 => 'ics',
                1 => 'ifb',
                2 => 'vcs',
                3 => 'icalendar',
              ],
          ],
        'text/css' =>
          [
            'desc' =>
              [
                0 => 'CSS stylesheet',
                1 => 'CSS: Cascading Style Sheets',
              ],
            'e' =>
              [
                0 => 'css',
              ],
          ],
        'text/csv' =>
          [
            'a' =>
              [
                0 => 'text/x-comma-separated-values',
                1 => 'text/x-csv',
              ],
            'desc' =>
              [
                0 => 'CSV document',
                1 => 'CSV: Comma Separated Values',
              ],
            'e' =>
              [
                0 => 'csv',
              ],
          ],
        'text/csv-schema' =>
          [
            'desc' =>
              [
                0 => 'CSV Schema document',
                1 => 'CSV: Comma Separated Values',
              ],
            'e' =>
              [
                0 => 'csvs',
              ],
          ],
        'text/html' =>
          [
            'desc' =>
              [
                0 => 'HTML document',
                1 => 'HTML: HyperText Markup Language',
              ],
            'e' =>
              [
                0 => 'html',
                1 => 'htm',
              ],
          ],
        'text/javascript' =>
          [
            'a' =>
              [
                0 => 'application/x-javascript',
                1 => 'application/javascript',
                2 => 'text/jscript',
              ],
            'desc' =>
              [
                0 => 'JavaScript program',
              ],
            'e' =>
              [
                0 => 'js',
                1 => 'mjs',
                2 => 'jsm',
              ],
          ],
        'text/jscript.encode' =>
          [
            'desc' =>
              [
                0 => 'Encoded JScript program',
              ],
            'e' =>
              [
                0 => 'jse',
              ],
          ],
        'text/julia' =>
          [
            'desc' =>
              [
                0 => 'Julia source code',
              ],
            'e' =>
              [
                0 => 'jl',
              ],
          ],
        'text/markdown' =>
          [
            'a' =>
              [
                0 => 'text/x-markdown',
              ],
            'desc' =>
              [
                0 => 'Markdown document',
              ],
            'e' =>
              [
                0 => 'md',
                1 => 'mkd',
                2 => 'markdown',
              ],
          ],
        'text/n3' =>
          [
            'e' =>
              [
                0 => 'n3',
              ],
          ],
        'text/org' =>
          [
            'desc' =>
              [
                0 => 'Org-mode file',
              ],
            'e' =>
              [
                0 => 'org',
              ],
          ],
        'text/plain' =>
          [
            'desc' =>
              [
                0 => 'Plain text document',
              ],
            'e' =>
              [
                0 => 'txt',
                1 => 'text',
                2 => 'conf',
                3 => 'def',
                4 => 'list',
                5 => 'log',
                6 => 'in',
                7 => 'asc',
              ],
          ],
        'text/prs.lines.tag' =>
          [
            'e' =>
              [
                0 => 'dsc',
              ],
          ],
        'text/richtext' =>
          [
            'desc' =>
              [
                0 => 'Rich text document',
              ],
            'e' =>
              [
                0 => 'rtx',
              ],
          ],
        'text/rust' =>
          [
            'desc' =>
              [
                0 => 'Rust source code',
              ],
            'e' =>
              [
                0 => 'rs',
              ],
          ],
        'text/sgml' =>
          [
            'desc' =>
              [
                0 => 'SGML document',
                1 => 'SGML: Standard Generalized Markup Language',
              ],
            'e' =>
              [
                0 => 'sgml',
                1 => 'sgm',
              ],
          ],
        'text/tab-separated-values' =>
          [
            'desc' =>
              [
                0 => 'TSV document',
                1 => 'TSV: Tab Separated Values',
              ],
            'e' =>
              [
                0 => 'tsv',
              ],
          ],
        'text/tcl' =>
          [
            'a' =>
              [
                0 => 'text/x-tcl',
              ],
            'desc' =>
              [
                0 => 'Tcl script',
              ],
            'e' =>
              [
                0 => 'tcl',
                1 => 'tk',
              ],
          ],
        'text/troff' =>
          [
            'a' =>
              [
                0 => 'application/x-troff',
                1 => 'text/x-troff',
              ],
            'desc' =>
              [
                0 => 'Troff document',
              ],
            'e' =>
              [
                0 => 't',
                1 => 'tr',
                2 => 'roff',
                3 => 'man',
                4 => 'me',
                5 => 'ms',
              ],
          ],
        'text/turtle' =>
          [
            'desc' =>
              [
                0 => 'Turtle document',
              ],
            'e' =>
              [
                0 => 'ttl',
              ],
          ],
        'text/uri-list' =>
          [
            'e' =>
              [
                0 => 'uri',
                1 => 'uris',
                2 => 'urls',
              ],
          ],
        'text/vbscript' =>
          [
            'a' =>
              [
                0 => 'text/vbs',
              ],
            'desc' =>
              [
                0 => 'VBScript program',
              ],
            'e' =>
              [
                0 => 'vbs',
              ],
          ],
        'text/vbscript.encode' =>
          [
            'desc' =>
              [
                0 => 'Encoded VBScript program',
              ],
            'e' =>
              [
                0 => 'vbe',
              ],
          ],
        'text/vcard' =>
          [
            'a' =>
              [
                0 => 'text/directory',
                1 => 'text/x-vcard',
              ],
            'desc' =>
              [
                0 => 'Electronic business card',
              ],
            'e' =>
              [
                0 => 'vcard',
                1 => 'vcf',
                2 => 'vct',
                3 => 'gcrd',
              ],
          ],
        'text/vnd.curl' =>
          [
            'e' =>
              [
                0 => 'curl',
              ],
          ],
        'text/vnd.curl.dcurl' =>
          [
            'e' =>
              [
                0 => 'dcurl',
              ],
          ],
        'text/vnd.curl.mcurl' =>
          [
            'e' =>
              [
                0 => 'mcurl',
              ],
          ],
        'text/vnd.curl.scurl' =>
          [
            'e' =>
              [
                0 => 'scurl',
              ],
          ],
        'text/vnd.dvb.subtitle' =>
          [
            'e' =>
              [
                0 => 'sub',
              ],
          ],
        'text/vnd.familysearch.gedcom' =>
          [
            'a' =>
              [
                0 => 'application/x-gedcom',
                1 => 'text/gedcom',
              ],
            'desc' =>
              [
                0 => 'GEDCOM family history',
                1 => 'GEDCOM: GEnealogical Data COMmunication',
              ],
            'e' =>
              [
                0 => 'ged',
                1 => 'gedcom',
              ],
          ],
        'text/vnd.fly' =>
          [
            'e' =>
              [
                0 => 'fly',
              ],
          ],
        'text/vnd.fmi.flexstor' =>
          [
            'e' =>
              [
                0 => 'flx',
              ],
          ],
        'text/vnd.graphviz' =>
          [
            'desc' =>
              [
                0 => 'Graphviz DOT graph',
              ],
            'e' =>
              [
                0 => 'gv',
                1 => 'dot',
              ],
          ],
        'text/vnd.in3d.3dml' =>
          [
            'e' =>
              [
                0 => '3dml',
              ],
          ],
        'text/vnd.in3d.spot' =>
          [
            'e' =>
              [
                0 => 'spot',
              ],
          ],
        'text/vnd.rn-realtext' =>
          [
            'desc' =>
              [
                0 => 'RealText document',
              ],
            'e' =>
              [
                0 => 'rt',
              ],
          ],
        'text/vnd.senx.warpscript' =>
          [
            'desc' =>
              [
                0 => 'WarpScript source code',
              ],
            'e' =>
              [
                0 => 'mc2',
              ],
          ],
        'text/vnd.sun.j2me.app-descriptor' =>
          [
            'desc' =>
              [
                0 => 'JAD document',
                1 => 'JAD: Java Application Descriptor',
              ],
            'e' =>
              [
                0 => 'jad',
              ],
          ],
        'text/vnd.trolltech.linguist' =>
          [
            'a' =>
              [
                0 => 'application/x-linguist',
                1 => 'text/vnd.qt.linguist',
              ],
            'desc' =>
              [
                0 => 'Message catalog',
              ],
            'e' =>
              [
                0 => 'ts',
              ],
          ],
        'text/vnd.wap.wml' =>
          [
            'desc' =>
              [
                0 => 'WML document',
                1 => 'WML: Wireless Markup Language',
              ],
            'e' =>
              [
                0 => 'wml',
              ],
          ],
        'text/vnd.wap.wmlscript' =>
          [
            'desc' =>
              [
                0 => 'WMLScript program',
              ],
            'e' =>
              [
                0 => 'wmls',
              ],
          ],
        'text/vtt' =>
          [
            'desc' =>
              [
                0 => 'WebVTT subtitles',
                1 => 'VTT: Video Text Tracks',
              ],
            'e' =>
              [
                0 => 'vtt',
              ],
          ],
        'text/x-adasrc' =>
          [
            'desc' =>
              [
                0 => 'Ada source code',
              ],
            'e' =>
              [
                0 => 'adb',
                1 => 'ads',
              ],
          ],
        'text/x-asm' =>
          [
            'desc' =>
              [
                0 => 'Assembly code',
              ],
            'e' =>
              [
                0 => 's',
                1 => 'asm',
              ],
          ],
        'text/x-basic' =>
          [
            'desc' =>
              [
                0 => 'BASIC program',
              ],
            'e' =>
              [
                0 => 'bas',
              ],
          ],
        'text/x-bibtex' =>
          [
            'desc' =>
              [
                0 => 'BibTeX document',
              ],
            'e' =>
              [
                0 => 'bib',
              ],
          ],
        'text/x-blueprint' =>
          [
            'desc' =>
              [
                0 => 'Blueprint source code',
              ],
            'e' =>
              [
                0 => 'blp',
              ],
          ],
        'text/x-c++hdr' =>
          [
            'desc' =>
              [
                0 => 'C++ header',
              ],
            'e' =>
              [
                0 => 'hh',
                1 => 'hp',
                2 => 'hpp',
                3 => 'h++',
                4 => 'hxx',
              ],
          ],
        'text/x-c++src' =>
          [
            'desc' =>
              [
                0 => 'C++ source code',
              ],
            'e' =>
              [
                0 => 'cpp',
                1 => 'cxx',
                2 => 'cc',
                3 => 'c',
                4 => 'c++',
              ],
          ],
        'text/x-chdr' =>
          [
            'desc' =>
              [
                0 => 'C header',
              ],
            'e' =>
              [
                0 => 'h',
              ],
          ],
        'text/x-cmake' =>
          [
            'desc' =>
              [
                0 => 'CMake source code',
              ],
            'e' =>
              [
                0 => 'cmake',
              ],
          ],
        'text/x-cobol' =>
          [
            'desc' =>
              [
                0 => 'COBOL source code',
                1 => 'COBOL: COmmon Business Oriented Language',
              ],
            'e' =>
              [
                0 => 'cbl',
                1 => 'cob',
              ],
          ],
        'text/x-common-lisp' =>
          [
            'desc' =>
              [
                0 => 'Common Lisp source code',
              ],
            'e' =>
              [
                0 => 'asd',
                1 => 'fasl',
                2 => 'lisp',
                3 => 'ros',
              ],
          ],
        'text/x-component' =>
          [
            'desc' =>
              [
                0 => 'HTML component',
                1 => 'HTML: HyperText Markup Language',
              ],
            'e' =>
              [
                0 => 'htc',
              ],
          ],
        'text/x-crystal' =>
          [
            'a' =>
              [
                0 => 'text/crystal',
              ],
            'desc' =>
              [
                0 => 'Crystal source code',
              ],
            'e' =>
              [
                0 => 'cr',
              ],
          ],
        'text/x-csharp' =>
          [
            'desc' =>
              [
                0 => 'C# source code',
              ],
            'e' =>
              [
                0 => 'cs',
              ],
          ],
        'text/x-csrc' =>
          [
            'a' =>
              [
                0 => 'text/x-c',
              ],
            'desc' =>
              [
                0 => 'C source code',
              ],
            'e' =>
              [
                0 => 'c',
                1 => 'dic',
              ],
          ],
        'text/x-cython' =>
          [
            'desc' =>
              [
                0 => 'Cython source code',
              ],
            'e' =>
              [
                0 => 'pxd',
                1 => 'pxi',
                2 => 'pyx',
              ],
          ],
        'text/x-dbus-service' =>
          [
            'desc' =>
              [
                0 => 'D-Bus service file',
              ],
            'e' =>
              [
                0 => 'service',
              ],
          ],
        'text/x-dcl' =>
          [
            'desc' =>
              [
                0 => 'DCL script',
                1 => 'DCL: Data Conversion Laboratory',
              ],
            'e' =>
              [
                0 => 'dcl',
              ],
          ],
        'text/x-devicetree-binary' =>
          [
            'desc' =>
              [
                0 => 'Flattened Devicetree',
                1 => 'DTB: Device Tree Binary',
              ],
            'e' =>
              [
                0 => 'dtb',
              ],
          ],
        'text/x-devicetree-source' =>
          [
            'desc' =>
              [
                0 => 'Devicetree source code',
                1 => 'DTS: Device Tree Source',
              ],
            'e' =>
              [
                0 => 'dts',
                1 => 'dtsi',
              ],
          ],
        'text/x-dsl' =>
          [
            'desc' =>
              [
                0 => 'DSSSL document',
                1 => 'DSSSL: Document Style Semantics and Specification Language',
              ],
            'e' =>
              [
                0 => 'dsl',
              ],
          ],
        'text/x-dsrc' =>
          [
            'desc' =>
              [
                0 => 'D source code',
              ],
            'e' =>
              [
                0 => 'd',
                1 => 'di',
              ],
          ],
        'text/x-eiffel' =>
          [
            'desc' =>
              [
                0 => 'Eiffel source code',
              ],
            'e' =>
              [
                0 => 'e',
                1 => 'eif',
              ],
          ],
        'text/x-elixir' =>
          [
            'desc' =>
              [
                0 => 'Elixir source code',
              ],
            'e' =>
              [
                0 => 'ex',
                1 => 'exs',
              ],
          ],
        'text/x-emacs-lisp' =>
          [
            'desc' =>
              [
                0 => 'Emacs Lisp source code',
              ],
            'e' =>
              [
                0 => 'el',
              ],
          ],
        'text/x-erlang' =>
          [
            'desc' =>
              [
                0 => 'Erlang source code',
              ],
            'e' =>
              [
                0 => 'erl',
              ],
          ],
        'text/x-fortran' =>
          [
            'desc' =>
              [
                0 => 'Fortran source code',
              ],
            'e' =>
              [
                0 => 'f',
                1 => 'for',
                2 => 'f77',
                3 => 'f90',
                4 => 'f95',
              ],
          ],
        'text/x-gcode-gx' =>
          [
            'desc' =>
              [
                0 => 'G-code Extended file',
              ],
            'e' =>
              [
                0 => 'gx',
              ],
          ],
        'text/x-genie' =>
          [
            'desc' =>
              [
                0 => 'Genie source code',
              ],
            'e' =>
              [
                0 => 'gs',
              ],
          ],
        'text/x-gettext-translation' =>
          [
            'a' =>
              [
                0 => 'text/x-po',
                1 => 'application/x-gettext',
              ],
            'desc' =>
              [
                0 => 'Translation file',
              ],
            'e' =>
              [
                0 => 'po',
              ],
          ],
        'text/x-gettext-translation-template' =>
          [
            'a' =>
              [
                0 => 'text/x-pot',
              ],
            'desc' =>
              [
                0 => 'Translation template',
              ],
            'e' =>
              [
                0 => 'pot',
              ],
          ],
        'text/x-gherkin' =>
          [
            'desc' =>
              [
                0 => 'Gherkin document',
              ],
            'e' =>
              [
                0 => 'feature',
              ],
          ],
        'text/x-go' =>
          [
            'desc' =>
              [
                0 => 'Go source code',
              ],
            'e' =>
              [
                0 => 'go',
              ],
          ],
        'text/x-google-video-pointer' =>
          [
            'a' =>
              [
                0 => 'text/google-video-pointer',
              ],
            'desc' =>
              [
                0 => 'Google Video Pointer shortcut',
              ],
            'e' =>
              [
                0 => 'gvp',
              ],
          ],
        'text/x-gradle' =>
          [
            'desc' =>
              [
                0 => 'Gradle script',
              ],
            'e' =>
              [
                0 => 'gradle',
              ],
          ],
        'text/x-groovy' =>
          [
            'desc' =>
              [
                0 => 'Groovy source code',
              ],
            'e' =>
              [
                0 => 'groovy',
                1 => 'gvy',
                2 => 'gy',
                3 => 'gsh',
              ],
          ],
        'text/x-haskell' =>
          [
            'desc' =>
              [
                0 => 'Haskell source code',
              ],
            'e' =>
              [
                0 => 'hs',
              ],
          ],
        'text/x-idl' =>
          [
            'desc' =>
              [
                0 => 'IDL document',
                1 => 'IDL: Interface Definition Language',
              ],
            'e' =>
              [
                0 => 'idl',
              ],
          ],
        'text/x-imelody' =>
          [
            'a' =>
              [
                0 => 'audio/x-imelody',
                1 => 'audio/imelody',
              ],
            'desc' =>
              [
                0 => 'iMelody ringtone',
              ],
            'e' =>
              [
                0 => 'imy',
                1 => 'ime',
              ],
          ],
        'text/x-iptables' =>
          [
            'desc' =>
              [
                0 => 'iptables configuration file',
              ],
            'e' =>
              [
                0 => 'iptables',
              ],
          ],
        'text/x-java' =>
          [
            'desc' =>
              [
                0 => 'Java source code',
              ],
            'e' =>
              [
                0 => 'java',
              ],
          ],
        'text/x-java-source' =>
          [
            'e' =>
              [
                0 => 'java',
              ],
          ],
        'text/x-kaitai-struct' =>
          [
            'desc' =>
              [
                0 => 'Kaitai Struct definition file',
              ],
            'e' =>
              [
                0 => 'ksy',
              ],
          ],
        'text/x-kotlin' =>
          [
            'desc' =>
              [
                0 => 'Kotlin source code',
              ],
            'e' =>
              [
                0 => 'kt',
              ],
          ],
        'text/x-ldif' =>
          [
            'desc' =>
              [
                0 => 'LDIF address book',
                1 => 'LDIF: LDAP Data Interchange Format',
              ],
            'e' =>
              [
                0 => 'ldif',
              ],
          ],
        'text/x-lilypond' =>
          [
            'desc' =>
              [
                0 => 'Lilypond music sheet',
              ],
            'e' =>
              [
                0 => 'ly',
              ],
          ],
        'text/x-literate-haskell' =>
          [
            'desc' =>
              [
                0 => 'LHS source code',
                1 => 'LHS: Literate Haskell source code',
              ],
            'e' =>
              [
                0 => 'lhs',
              ],
          ],
        'text/x-log' =>
          [
            'desc' =>
              [
                0 => 'Application log',
              ],
            'e' =>
              [
                0 => 'log',
              ],
          ],
        'text/x-lua' =>
          [
            'desc' =>
              [
                0 => 'Lua script',
              ],
            'e' =>
              [
                0 => 'lua',
              ],
          ],
        'text/x-makefile' =>
          [
            'desc' =>
              [
                0 => 'Makefile build file',
              ],
            'e' =>
              [
                0 => 'mk',
                1 => 'mak',
              ],
          ],
        'text/x-matlab' =>
          [
            'a' =>
              [
                0 => 'text/x-octave',
              ],
            'desc' =>
              [
                0 => 'MATLAB file',
              ],
            'e' =>
              [
                0 => 'm',
              ],
          ],
        'text/x-microdvd' =>
          [
            'desc' =>
              [
                0 => 'MicroDVD subtitles',
              ],
            'e' =>
              [
                0 => 'sub',
              ],
          ],
        'text/x-moc' =>
          [
            'desc' =>
              [
                0 => 'Qt MOC file',
                1 => 'Qt MOC: Qt Meta Object Compiler',
              ],
            'e' =>
              [
                0 => 'moc',
              ],
          ],
        'text/x-modelica' =>
          [
            'desc' =>
              [
                0 => 'Modelica model',
              ],
            'e' =>
              [
                0 => 'mo',
              ],
          ],
        'text/x-mof' =>
          [
            'desc' =>
              [
                0 => 'MOF file',
                1 => 'MOF: Windows Managed Object File',
              ],
            'e' =>
              [
                0 => 'mof',
              ],
          ],
        'text/x-mpl2' =>
          [
            'desc' =>
              [
                0 => 'MPL2 subtitles',
              ],
            'e' =>
              [
                0 => 'mpl',
              ],
          ],
        'text/x-mpsub' =>
          [
            'desc' =>
              [
                0 => 'MPlayer subtitles',
              ],
            'e' =>
              [
                0 => 'sub',
              ],
          ],
        'text/x-mrml' =>
          [
            'desc' =>
              [
                0 => 'MRML playlist',
                1 => 'MRML: Multimedia Retrieval Markup Language',
              ],
            'e' =>
              [
                0 => 'mrml',
                1 => 'mrl',
              ],
          ],
        'text/x-ms-regedit' =>
          [
            'desc' =>
              [
                0 => 'Windows Registry extract',
              ],
            'e' =>
              [
                0 => 'reg',
              ],
          ],
        'text/x-mup' =>
          [
            'desc' =>
              [
                0 => 'Mup musical composition document',
              ],
            'e' =>
              [
                0 => 'mup',
                1 => 'not',
              ],
          ],
        'text/x-nfo' =>
          [
            'desc' =>
              [
                0 => 'NFO document',
              ],
            'e' =>
              [
                0 => 'nfo',
              ],
          ],
        'text/x-nim' =>
          [
            'desc' =>
              [
                0 => 'Nim source code',
              ],
            'e' =>
              [
                0 => 'nim',
              ],
          ],
        'text/x-nimscript' =>
          [
            'desc' =>
              [
                0 => 'Nimscript source code',
              ],
            'e' =>
              [
                0 => 'nims',
                1 => 'nimble',
              ],
          ],
        'text/x-nix' =>
          [
            'desc' =>
              [
                0 => 'Nix source code',
              ],
            'e' =>
              [
                0 => 'nix',
              ],
          ],
        'text/x-objc++src' =>
          [
            'desc' =>
              [
                0 => 'Objective-C++ source code',
              ],
            'e' =>
              [
                0 => 'mm',
              ],
          ],
        'text/x-objcsrc' =>
          [
            'desc' =>
              [
                0 => 'Objective-C source code',
              ],
            'e' =>
              [
                0 => 'm',
              ],
          ],
        'text/x-ocaml' =>
          [
            'desc' =>
              [
                0 => 'OCaml source code',
              ],
            'e' =>
              [
                0 => 'ml',
                1 => 'mli',
              ],
          ],
        'text/x-ocl' =>
          [
            'desc' =>
              [
                0 => 'OCL file',
                1 => 'OCL: Object Constraint Language',
              ],
            'e' =>
              [
                0 => 'ocl',
              ],
          ],
        'text/x-ooc' =>
          [
            'desc' =>
              [
                0 => 'OOC source code',
                1 => 'OOC: Out Of Class',
              ],
            'e' =>
              [
                0 => 'ooc',
              ],
          ],
        'text/x-opencl-src' =>
          [
            'desc' =>
              [
                0 => 'OpenCL source code',
                1 => 'OpenCL: Open Computing Language',
              ],
            'e' =>
              [
                0 => 'cl',
              ],
          ],
        'text/x-opml+xml' =>
          [
            'a' =>
              [
                0 => 'text/x-opml',
              ],
            'desc' =>
              [
                0 => 'OPML syndication feed',
                1 => 'OPML: Outline Processor Markup Language',
              ],
            'e' =>
              [
                0 => 'opml',
              ],
          ],
        'text/x-pascal' =>
          [
            'desc' =>
              [
                0 => 'Pascal source code',
              ],
            'e' =>
              [
                0 => 'p',
                1 => 'pas',
              ],
          ],
        'text/x-patch' =>
          [
            'a' =>
              [
                0 => 'text/x-diff',
              ],
            'desc' =>
              [
                0 => 'Differences between files',
              ],
            'e' =>
              [
                0 => 'diff',
                1 => 'patch',
              ],
          ],
        'text/x-python' =>
          [
            'desc' =>
              [
                0 => 'Python script',
              ],
            'e' =>
              [
                0 => 'py',
                1 => 'wsgi',
              ],
          ],
        'text/x-python2' =>
          [
            'desc' =>
              [
                0 => 'Python 2 script',
              ],
            'e' =>
              [
                0 => 'py',
                1 => 'py2',
              ],
          ],
        'text/x-python3' =>
          [
            'desc' =>
              [
                0 => 'Python 3 script',
              ],
            'e' =>
              [
                0 => 'py',
                1 => 'py3',
                2 => 'pyi',
              ],
          ],
        'text/x-qml' =>
          [
            'desc' =>
              [
                0 => 'Qt Markup Language file',
              ],
            'e' =>
              [
                0 => 'qml',
                1 => 'qmltypes',
                2 => 'qmlproject',
              ],
          ],
        'text/x-reject' =>
          [
            'a' =>
              [
                0 => 'application/x-reject',
              ],
            'desc' =>
              [
                0 => 'Rejected patch',
              ],
            'e' =>
              [
                0 => 'rej',
              ],
          ],
        'text/x-rpm-spec' =>
          [
            'desc' =>
              [
                0 => 'RPM spec file',
                1 => 'RPM: Red Hat Package Manager',
              ],
            'e' =>
              [
                0 => 'spec',
              ],
          ],
        'text/x-rst' =>
          [
            'desc' =>
              [
                0 => 'ReStructuredText document',
              ],
            'e' =>
              [
                0 => 'rst',
              ],
          ],
        'text/x-sagemath' =>
          [
            'desc' =>
              [
                0 => 'SageMath script',
              ],
            'e' =>
              [
                0 => 'sage',
              ],
          ],
        'text/x-sass' =>
          [
            'desc' =>
              [
                0 => 'Sass CSS pre-processor file',
                1 => 'Sass: Syntactically Awesome Style Sheets',
              ],
            'e' =>
              [
                0 => 'sass',
              ],
          ],
        'text/x-scala' =>
          [
            'desc' =>
              [
                0 => 'Scala source code',
              ],
            'e' =>
              [
                0 => 'scala',
                1 => 'sc',
              ],
          ],
        'text/x-scheme' =>
          [
            'desc' =>
              [
                0 => 'Scheme source code',
              ],
            'e' =>
              [
                0 => 'scm',
                1 => 'ss',
              ],
          ],
        'text/x-scss' =>
          [
            'desc' =>
              [
                0 => 'SCSS pre-processor file',
                1 => 'SCSS: Sassy CSS',
              ],
            'e' =>
              [
                0 => 'scss',
              ],
          ],
        'text/x-setext' =>
          [
            'desc' =>
              [
                0 => 'Setext document',
              ],
            'e' =>
              [
                0 => 'etx',
              ],
          ],
        'text/x-sfv' =>
          [
            'e' =>
              [
                0 => 'sfv',
              ],
          ],
        'text/x-ssa' =>
          [
            'desc' =>
              [
                0 => 'SSA subtitles',
                1 => 'SSA: SubStation Alpha',
              ],
            'e' =>
              [
                0 => 'ssa',
                1 => 'ass',
              ],
          ],
        'text/x-subviewer' =>
          [
            'desc' =>
              [
                0 => 'SubViewer subtitles',
              ],
            'e' =>
              [
                0 => 'sub',
              ],
          ],
        'text/x-svhdr' =>
          [
            'desc' =>
              [
                0 => 'SystemVerilog header',
              ],
            'e' =>
              [
                0 => 'svh',
              ],
          ],
        'text/x-svsrc' =>
          [
            'desc' =>
              [
                0 => 'SystemVerilog source code',
              ],
            'e' =>
              [
                0 => 'sv',
              ],
          ],
        'text/x-systemd-unit' =>
          [
            'desc' =>
              [
                0 => 'Systemd unit file',
              ],
            'e' =>
              [
                0 => 'automount',
                1 => 'device',
                2 => 'mount',
                3 => 'path',
                4 => 'scope',
                5 => 'service',
                6 => 'slice',
                7 => 'socket',
                8 => 'swap',
                9 => 'target',
                10 => 'timer',
              ],
          ],
        'text/x-tex' =>
          [
            'a' =>
              [
                0 => 'application/x-tex',
              ],
            'desc' =>
              [
                0 => 'TeX document',
              ],
            'e' =>
              [
                0 => 'tex',
                1 => 'ltx',
                2 => 'sty',
                3 => 'cls',
                4 => 'dtx',
                5 => 'ins',
                6 => 'latex',
              ],
          ],
        'text/x-texinfo' =>
          [
            'desc' =>
              [
                0 => 'TeXInfo document',
              ],
            'e' =>
              [
                0 => 'texi',
                1 => 'texinfo',
              ],
          ],
        'text/x-troff-me' =>
          [
            'desc' =>
              [
                0 => 'Troff ME input document',
              ],
            'e' =>
              [
                0 => 'me',
              ],
          ],
        'text/x-troff-mm' =>
          [
            'desc' =>
              [
                0 => 'Troff MM input document',
              ],
            'e' =>
              [
                0 => 'mm',
              ],
          ],
        'text/x-troff-ms' =>
          [
            'desc' =>
              [
                0 => 'Troff MS input document',
              ],
            'e' =>
              [
                0 => 'ms',
              ],
          ],
        'text/x-twig' =>
          [
            'desc' =>
              [
                0 => 'Twig template',
              ],
            'e' =>
              [
                0 => 'twig',
              ],
          ],
        'text/x-txt2tags' =>
          [
            'desc' =>
              [
                0 => 'txt2tags document',
              ],
            'e' =>
              [
                0 => 't2t',
              ],
          ],
        'text/x-typst' =>
          [
            'desc' =>
              [
                0 => 'Typst document',
              ],
            'e' =>
              [
                0 => 'typ',
              ],
          ],
        'text/x-uil' =>
          [
            'desc' =>
              [
                0 => 'X-Motif UIL table',
              ],
            'e' =>
              [
                0 => 'uil',
              ],
          ],
        'text/x-uuencode' =>
          [
            'a' =>
              [
                0 => 'zz-application/zz-winassoc-uu',
              ],
            'desc' =>
              [
                0 => 'uuencoded file',
              ],
            'e' =>
              [
                0 => 'uu',
                1 => 'uue',
              ],
          ],
        'text/x-vala' =>
          [
            'desc' =>
              [
                0 => 'Vala source code',
              ],
            'e' =>
              [
                0 => 'vala',
                1 => 'vapi',
              ],
          ],
        'text/x-vb' =>
          [
            'desc' =>
              [
                0 => 'Visual Basic .NET source code',
              ],
            'e' =>
              [
                0 => 'vb',
              ],
          ],
        'text/x-verilog' =>
          [
            'desc' =>
              [
                0 => 'Verilog source code',
              ],
            'e' =>
              [
                0 => 'v',
              ],
          ],
        'text/x-vhdl' =>
          [
            'desc' =>
              [
                0 => 'VHDL source code',
                1 => 'VHDL: Very-High-Speed Integrated Circuit Hardware Description Language',
              ],
            'e' =>
              [
                0 => 'vhd',
                1 => 'vhdl',
              ],
          ],
        'text/x-xmi' =>
          [
            'desc' =>
              [
                0 => 'XMI file',
                1 => 'XMI: XML Metadata Interchange',
              ],
            'e' =>
              [
                0 => 'xmi',
              ],
          ],
        'text/x-xslfo' =>
          [
            'desc' =>
              [
                0 => 'XSL FO file',
                1 => 'XSL FO: XSL Formatting Objects',
              ],
            'e' =>
              [
                0 => 'fo',
                1 => 'xslfo',
              ],
          ],
        'text/x.gcode' =>
          [
            'desc' =>
              [
                0 => 'G-code file',
              ],
            'e' =>
              [
                0 => 'gcode',
              ],
          ],
        'video/3gpp' =>
          [
            'a' =>
              [
                0 => 'video/3gp',
                1 => 'audio/3gpp',
                2 => 'video/3gpp-encrypted',
                3 => 'audio/3gpp-encrypted',
                4 => 'audio/x-rn-3gpp-amr',
                5 => 'audio/x-rn-3gpp-amr-encrypted',
                6 => 'audio/x-rn-3gpp-amr-wb',
                7 => 'audio/x-rn-3gpp-amr-wb-encrypted',
              ],
            'desc' =>
              [
                0 => '3GPP multimedia file',
                1 => '3GPP: 3rd Generation Partnership Project',
              ],
            'e' =>
              [
                0 => '3gp',
                1 => '3gpp',
                2 => '3ga',
              ],
          ],
        'video/3gpp2' =>
          [
            'a' =>
              [
                0 => 'audio/3gpp2',
              ],
            'desc' =>
              [
                0 => '3GPP2 multimedia file',
                1 => '3GPP2: 3rd Generation Partnership Project 2',
              ],
            'e' =>
              [
                0 => '3g2',
                1 => '3gp2',
                2 => '3gpp2',
              ],
          ],
        'video/annodex' =>
          [
            'a' =>
              [
                0 => 'video/x-annodex',
              ],
            'desc' =>
              [
                0 => 'Annodex video',
              ],
            'e' =>
              [
                0 => 'axv',
              ],
          ],
        'video/dv' =>
          [
            'desc' =>
              [
                0 => 'DV video',
                1 => 'DV: Digital Video',
              ],
            'e' =>
              [
                0 => 'dv',
              ],
          ],
        'video/h261' =>
          [
            'e' =>
              [
                0 => 'h261',
              ],
          ],
        'video/h263' =>
          [
            'e' =>
              [
                0 => 'h263',
              ],
          ],
        'video/h264' =>
          [
            'e' =>
              [
                0 => 'h264',
              ],
          ],
        'video/jpeg' =>
          [
            'e' =>
              [
                0 => 'jpgv',
              ],
          ],
        'video/jpm' =>
          [
            'e' =>
              [
                0 => 'jpm',
                1 => 'jpgm',
              ],
          ],
        'video/mj2' =>
          [
            'desc' =>
              [
                0 => 'JPEG-2000 MJ2 video',
                1 => 'MJ2: Motion JPEG-2000',
              ],
            'e' =>
              [
                0 => 'mj2',
                1 => 'mjp2',
              ],
          ],
        'video/mp2t' =>
          [
            'desc' =>
              [
                0 => 'MPEG-2 transport stream',
                1 => 'MPEG-2 TS: Moving Picture Experts Group 2 Transport Stream',
              ],
            'e' =>
              [
                0 => 'ts',
                1 => 'm2t',
                2 => 'm2ts',
                3 => 'mts',
                4 => 'cpi',
                5 => 'clpi',
                6 => 'mpl',
                7 => 'mpls',
                8 => 'bdm',
                9 => 'bdmv',
              ],
          ],
        'video/mp4' =>
          [
            'a' =>
              [
                0 => 'video/mp4v-es',
                1 => 'video/x-m4v',
              ],
            'desc' =>
              [
                0 => 'MPEG-4 video',
              ],
            'e' =>
              [
                0 => 'mp4',
                1 => 'mp4v',
                2 => 'mpg4',
                3 => 'm4v',
                4 => 'f4v',
                5 => 'lrv',
              ],
          ],
        'video/mpeg' =>
          [
            'a' =>
              [
                0 => 'video/x-mpeg',
                1 => 'video/mpeg-system',
                2 => 'video/x-mpeg-system',
                3 => 'video/x-mpeg2',
              ],
            'desc' =>
              [
                0 => 'MPEG video',
                1 => 'MPEG: Moving Picture Experts Group',
              ],
            'e' =>
              [
                0 => 'mpeg',
                1 => 'mpg',
                2 => 'mpe',
                3 => 'm1v',
                4 => 'm2v',
                5 => 'mp2',
                6 => 'vob',
              ],
          ],
        'video/ogg' =>
          [
            'a' =>
              [
                0 => 'video/x-ogg',
              ],
            'desc' =>
              [
                0 => 'Ogg video',
              ],
            'e' =>
              [
                0 => 'ogv',
                1 => 'ogg',
              ],
          ],
        'video/quicktime' =>
          [
            'desc' =>
              [
                0 => 'QuickTime video',
              ],
            'e' =>
              [
                0 => 'qt',
                1 => 'mov',
                2 => 'moov',
                3 => 'qtvr',
              ],
          ],
        'video/vnd.avi' =>
          [
            'a' =>
              [
                0 => 'video/x-avi',
                1 => 'video/avi',
                2 => 'video/divx',
                3 => 'video/msvideo',
                4 => 'video/vnd.divx',
                5 => 'video/x-msvideo',
              ],
            'desc' =>
              [
                0 => 'AVI video',
                1 => 'AVI: Audio Video Interleave',
              ],
            'e' =>
              [
                0 => 'avi',
                1 => 'avf',
                2 => 'divx',
              ],
          ],
        'video/vnd.dece.hd' =>
          [
            'e' =>
              [
                0 => 'uvh',
                1 => 'uvvh',
              ],
          ],
        'video/vnd.dece.mobile' =>
          [
            'e' =>
              [
                0 => 'uvm',
                1 => 'uvvm',
              ],
          ],
        'video/vnd.dece.pd' =>
          [
            'e' =>
              [
                0 => 'uvp',
                1 => 'uvvp',
              ],
          ],
        'video/vnd.dece.sd' =>
          [
            'e' =>
              [
                0 => 'uvs',
                1 => 'uvvs',
              ],
          ],
        'video/vnd.dece.video' =>
          [
            'e' =>
              [
                0 => 'uvv',
                1 => 'uvvv',
              ],
          ],
        'video/vnd.dvb.file' =>
          [
            'e' =>
              [
                0 => 'dvb',
              ],
          ],
        'video/vnd.fvt' =>
          [
            'e' =>
              [
                0 => 'fvt',
              ],
          ],
        'video/vnd.mpegurl' =>
          [
            'a' =>
              [
                0 => 'video/x-mpegurl',
              ],
            'desc' =>
              [
                0 => 'Video playlist',
              ],
            'e' =>
              [
                0 => 'mxu',
                1 => 'm4u',
                2 => 'm1u',
              ],
          ],
        'video/vnd.ms-playready.media.pyv' =>
          [
            'e' =>
              [
                0 => 'pyv',
              ],
          ],
        'video/vnd.radgamettools.bink' =>
          [
            'desc' =>
              [
                0 => 'Bink Video',
              ],
            'e' =>
              [
                0 => 'bik',
                1 => 'bk2',
              ],
          ],
        'video/vnd.radgamettools.smacker' =>
          [
            'desc' =>
              [
                0 => 'Smacker Video',
              ],
            'e' =>
              [
                0 => 'smk',
              ],
          ],
        'video/vnd.rn-realvideo' =>
          [
            'a' =>
              [
                0 => 'video/x-real-video',
              ],
            'desc' =>
              [
                0 => 'RealVideo document',
              ],
            'e' =>
              [
                0 => 'rv',
                1 => 'rvx',
              ],
          ],
        'video/vnd.uvvu.mp4' =>
          [
            'e' =>
              [
                0 => 'uvu',
                1 => 'uvvu',
              ],
          ],
        'video/vnd.vivo' =>
          [
            'a' =>
              [
                0 => 'video/vivo',
              ],
            'desc' =>
              [
                0 => 'Vivo video',
              ],
            'e' =>
              [
                0 => 'viv',
                1 => 'vivo',
              ],
          ],
        'video/vnd.youtube.yt' =>
          [
            'a' =>
              [
                0 => 'application/vnd.youtube.yt',
              ],
            'desc' =>
              [
                0 => 'YouTube media archive',
              ],
            'e' =>
              [
                0 => 'yt',
              ],
          ],
        'video/webm' =>
          [
            'desc' =>
              [
                0 => 'WebM video',
              ],
            'e' =>
              [
                0 => 'webm',
              ],
          ],
        'video/x-anim' =>
          [
            'desc' =>
              [
                0 => 'ANIM animation',
              ],
            'e' =>
              [
                0 => 'anim1',
                1 => 'anim2',
                2 => 'anim3',
                3 => 'anim4',
                4 => 'anim5',
                5 => 'anim6',
                6 => 'anim7',
                7 => 'anim8',
                8 => 'anim9',
                9 => 'animj',
              ],
          ],
        'video/x-f4v' =>
          [
            'e' =>
              [
                0 => 'f4v',
              ],
          ],
        'video/x-flic' =>
          [
            'a' =>
              [
                0 => 'video/fli',
                1 => 'video/x-fli',
              ],
            'desc' =>
              [
                0 => 'FLIC animation',
              ],
            'e' =>
              [
                0 => 'fli',
                1 => 'flc',
              ],
          ],
        'video/x-flv' =>
          [
            'a' =>
              [
                0 => 'application/x-flash-video',
                1 => 'flv-application/octet-stream',
                2 => 'video/flv',
              ],
            'desc' =>
              [
                0 => 'Flash video',
              ],
            'e' =>
              [
                0 => 'flv',
              ],
          ],
        'video/x-javafx' =>
          [
            'desc' =>
              [
                0 => 'JavaFX video',
              ],
            'e' =>
              [
                0 => 'fxm',
              ],
          ],
        'video/x-matroska' =>
          [
            'desc' =>
              [
                0 => 'Matroska video',
              ],
            'e' =>
              [
                0 => 'mkv',
                1 => 'mk3d',
                2 => 'mks',
              ],
          ],
        'video/x-matroska-3d' =>
          [
            'desc' =>
              [
                0 => 'Matroska 3D video',
              ],
            'e' =>
              [
                0 => 'mk3d',
              ],
          ],
        'video/x-mjpeg' =>
          [
            'desc' =>
              [
                0 => 'MJPEG video stream',
                1 => 'MJPEG: Motion JPEG',
              ],
            'e' =>
              [
                0 => 'mjpeg',
                1 => 'mjpg',
              ],
          ],
        'video/x-mng' =>
          [
            'desc' =>
              [
                0 => 'MNG animation',
                1 => 'MNG: Multiple-Image Network Graphics',
              ],
            'e' =>
              [
                0 => 'mng',
              ],
          ],
        'video/x-ms-vob' =>
          [
            'e' =>
              [
                0 => 'vob',
              ],
          ],
        'video/x-ms-wmv' =>
          [
            'desc' =>
              [
                0 => 'Windows Media video',
              ],
            'e' =>
              [
                0 => 'wmv',
              ],
          ],
        'video/x-nsv' =>
          [
            'desc' =>
              [
                0 => 'NullSoft video',
              ],
            'e' =>
              [
                0 => 'nsv',
              ],
          ],
        'video/x-ogm+ogg' =>
          [
            'a' =>
              [
                0 => 'video/x-ogm',
              ],
            'desc' =>
              [
                0 => 'OGM video',
              ],
            'e' =>
              [
                0 => 'ogm',
              ],
          ],
        'video/x-sgi-movie' =>
          [
            'desc' =>
              [
                0 => 'SGI video',
              ],
            'e' =>
              [
                0 => 'movie',
              ],
          ],
        'video/x-smv' =>
          [
            'e' =>
              [
                0 => 'smv',
              ],
          ],
        'video/x-theora+ogg' =>
          [
            'a' =>
              [
                0 => 'video/x-theora',
              ],
            'desc' =>
              [
                0 => 'Ogg Theora video',
              ],
            'e' =>
              [
                0 => 'ogg',
              ],
          ],
        'x-conference/x-cooltalk' =>
          [
            'e' =>
              [
                0 => 'ice',
              ],
          ],
        'x-epoc/x-sisx-app' =>
          [
            'desc' =>
              [
                0 => 'SISX package',
                1 => 'SIS: Symbian Installation File',
              ],
            'e' =>
              [
                0 => 'sisx',
              ],
          ],
      ],
    'e' =>
      [
        123 =>
          [
            't' =>
              [
                0 => 'application/vnd.lotus-1-2-3',
              ],
          ],
        '32x' =>
          [
            't' =>
              [
                0 => 'application/x-genesis-32x-rom',
              ],
          ],
        '3dml' =>
          [
            't' =>
              [
                0 => 'text/vnd.in3d.3dml',
              ],
          ],
        '3ds' =>
          [
            't' =>
              [
                0 => 'image/x-3ds',
                1 => 'application/x-nintendo-3ds-rom',
              ],
          ],
        '3dsx' =>
          [
            't' =>
              [
                0 => 'application/x-nintendo-3ds-executable',
              ],
          ],
        '3g2' =>
          [
            't' =>
              [
                0 => 'video/3gpp2',
              ],
          ],
        '3ga' =>
          [
            't' =>
              [
                0 => 'video/3gpp',
              ],
          ],
        '3gp' =>
          [
            't' =>
              [
                0 => 'video/3gpp',
              ],
          ],
        '3gp2' =>
          [
            't' =>
              [
                0 => 'video/3gpp2',
              ],
          ],
        '3gpp' =>
          [
            't' =>
              [
                0 => 'video/3gpp',
              ],
          ],
        '3gpp2' =>
          [
            't' =>
              [
                0 => 'video/3gpp2',
              ],
          ],
        '3mf' =>
          [
            't' =>
              [
                0 => 'model/3mf',
              ],
          ],
        602 =>
          [
            't' =>
              [
                0 => 'application/x-t602',
              ],
          ],
        669 =>
          [
            't' =>
              [
                0 => 'audio/x-mod',
              ],
          ],
        '7z' =>
          [
            't' =>
              [
                0 => 'application/x-7z-compressed',
              ],
          ],
        '7z.001' =>
          [
            't' =>
              [
                0 => 'application/x-7z-compressed',
              ],
          ],
        'a' =>
          [
            't' =>
              [
                0 => 'application/x-archive',
              ],
          ],
        'a26' =>
          [
            't' =>
              [
                0 => 'application/x-atari-2600-rom',
              ],
          ],
        'a78' =>
          [
            't' =>
              [
                0 => 'application/x-atari-7800-rom',
              ],
          ],
        'aa' =>
          [
            't' =>
              [
                0 => 'audio/x-pn-audibleaudio',
              ],
          ],
        'aab' =>
          [
            't' =>
              [
                0 => 'application/x-authorware-bin',
              ],
          ],
        'aac' =>
          [
            't' =>
              [
                0 => 'audio/aac',
              ],
          ],
        'aam' =>
          [
            't' =>
              [
                0 => 'application/x-authorware-map',
              ],
          ],
        'aas' =>
          [
            't' =>
              [
                0 => 'application/x-authorware-seg',
              ],
          ],
        'aax' =>
          [
            't' =>
              [
                0 => 'audio/vnd.audible.aax',
              ],
          ],
        'aaxc' =>
          [
            't' =>
              [
                0 => 'audio/vnd.audible.aaxc',
              ],
          ],
        'abw' =>
          [
            't' =>
              [
                0 => 'application/x-abiword',
              ],
          ],
        'abw.crashed' =>
          [
            't' =>
              [
                0 => 'application/x-abiword',
              ],
          ],
        'abw.gz' =>
          [
            't' =>
              [
                0 => 'application/x-abiword',
              ],
          ],
        'ac' =>
          [
            't' =>
              [
                0 => 'application/pkix-attr-cert',
              ],
          ],
        'ac3' =>
          [
            't' =>
              [
                0 => 'audio/ac3',
              ],
          ],
        'acc' =>
          [
            't' =>
              [
                0 => 'application/vnd.americandynamics.acc',
              ],
          ],
        'ace' =>
          [
            't' =>
              [
                0 => 'application/x-ace-compressed',
                1 => 'application/x-ace',
              ],
          ],
        'acu' =>
          [
            't' =>
              [
                0 => 'application/vnd.acucobol',
              ],
          ],
        'acutc' =>
          [
            't' =>
              [
                0 => 'application/vnd.acucorp',
              ],
          ],
        'adb' =>
          [
            't' =>
              [
                0 => 'text/x-adasrc',
              ],
          ],
        'adf' =>
          [
            't' =>
              [
                0 => 'application/x-amiga-disk-format',
              ],
          ],
        'adp' =>
          [
            't' =>
              [
                0 => 'audio/adpcm',
              ],
          ],
        'ads' =>
          [
            't' =>
              [
                0 => 'text/x-adasrc',
              ],
          ],
        'adts' =>
          [
            't' =>
              [
                0 => 'audio/aac',
              ],
          ],
        'aep' =>
          [
            't' =>
              [
                0 => 'application/vnd.audiograph',
              ],
          ],
        'afm' =>
          [
            't' =>
              [
                0 => 'application/x-font-type1',
                1 => 'application/x-font-afm',
              ],
          ],
        'afp' =>
          [
            't' =>
              [
                0 => 'application/vnd.ibm.modcap',
              ],
          ],
        'ag' =>
          [
            't' =>
              [
                0 => 'image/x-applix-graphics',
              ],
          ],
        'agb' =>
          [
            't' =>
              [
                0 => 'application/x-gba-rom',
              ],
          ],
        'ahead' =>
          [
            't' =>
              [
                0 => 'application/vnd.ahead.space',
              ],
          ],
        'ai' =>
          [
            't' =>
              [
                0 => 'application/postscript',
                1 => 'application/illustrator',
              ],
          ],
        'aif' =>
          [
            't' =>
              [
                0 => 'audio/x-aiff',
              ],
          ],
        'aifc' =>
          [
            't' =>
              [
                0 => 'audio/x-aiff',
                1 => 'audio/x-aifc',
              ],
          ],
        'aiff' =>
          [
            't' =>
              [
                0 => 'audio/x-aiff',
              ],
          ],
        'aiffc' =>
          [
            't' =>
              [
                0 => 'audio/x-aifc',
              ],
          ],
        'air' =>
          [
            't' =>
              [
                0 => 'application/vnd.adobe.air-application-installer-package+zip',
              ],
          ],
        'ait' =>
          [
            't' =>
              [
                0 => 'application/vnd.dvb.ait',
              ],
          ],
        'al' =>
          [
            't' =>
              [
                0 => 'application/x-perl',
              ],
          ],
        'alz' =>
          [
            't' =>
              [
                0 => 'application/x-alz',
              ],
          ],
        'ami' =>
          [
            't' =>
              [
                0 => 'application/vnd.amiga.ami',
              ],
          ],
        'amr' =>
          [
            't' =>
              [
                0 => 'audio/amr',
              ],
          ],
        'amz' =>
          [
            't' =>
              [
                0 => 'audio/x-amzxml',
              ],
          ],
        'ani' =>
          [
            't' =>
              [
                0 => 'application/x-navi-animation',
              ],
          ],
        'anim1' =>
          [
            't' =>
              [
                0 => 'video/x-anim',
              ],
          ],
        'anim2' =>
          [
            't' =>
              [
                0 => 'video/x-anim',
              ],
          ],
        'anim3' =>
          [
            't' =>
              [
                0 => 'video/x-anim',
              ],
          ],
        'anim4' =>
          [
            't' =>
              [
                0 => 'video/x-anim',
              ],
          ],
        'anim5' =>
          [
            't' =>
              [
                0 => 'video/x-anim',
              ],
          ],
        'anim6' =>
          [
            't' =>
              [
                0 => 'video/x-anim',
              ],
          ],
        'anim7' =>
          [
            't' =>
              [
                0 => 'video/x-anim',
              ],
          ],
        'anim8' =>
          [
            't' =>
              [
                0 => 'video/x-anim',
              ],
          ],
        'anim9' =>
          [
            't' =>
              [
                0 => 'video/x-anim',
              ],
          ],
        'animj' =>
          [
            't' =>
              [
                0 => 'video/x-anim',
              ],
          ],
        'anx' =>
          [
            't' =>
              [
                0 => 'application/annodex',
              ],
          ],
        'ape' =>
          [
            't' =>
              [
                0 => 'audio/x-ape',
              ],
          ],
        'apk' =>
          [
            't' =>
              [
                0 => 'application/vnd.android.package-archive',
              ],
          ],
        'apng' =>
          [
            't' =>
              [
                0 => 'image/apng',
              ],
          ],
        'appcache' =>
          [
            't' =>
              [
                0 => 'text/cache-manifest',
              ],
          ],
        'appimage' =>
          [
            't' =>
              [
                0 => 'application/x-iso9660-appimage',
                1 => 'application/vnd.appimage',
              ],
          ],
        'appinstaller' =>
          [
            't' =>
              [
                0 => 'application/appinstaller',
              ],
          ],
        'application' =>
          [
            't' =>
              [
                0 => 'application/x-ms-application',
              ],
          ],
        'appx' =>
          [
            't' =>
              [
                0 => 'application/appx',
              ],
          ],
        'appxbundle' =>
          [
            't' =>
              [
                0 => 'application/appxbundle',
              ],
          ],
        'apr' =>
          [
            't' =>
              [
                0 => 'application/vnd.lotus-approach',
              ],
          ],
        'ar' =>
          [
            't' =>
              [
                0 => 'application/x-archive',
              ],
          ],
        'arc' =>
          [
            't' =>
              [
                0 => 'application/x-freearc',
              ],
          ],
        'arj' =>
          [
            't' =>
              [
                0 => 'application/x-arj',
              ],
          ],
        'arw' =>
          [
            't' =>
              [
                0 => 'image/x-sony-arw',
              ],
          ],
        'as' =>
          [
            't' =>
              [
                0 => 'application/x-applix-spreadsheet',
              ],
          ],
        'asar' =>
          [
            't' =>
              [
                0 => 'application/x-asar',
              ],
          ],
        'asc' =>
          [
            't' =>
              [
                0 => 'application/pgp-signature',
                1 => 'application/pgp-encrypted',
                2 => 'application/pgp-keys',
                3 => 'text/plain',
              ],
          ],
        'asd' =>
          [
            't' =>
              [
                0 => 'text/x-common-lisp',
              ],
          ],
        'asf' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-asf',
              ],
          ],
        'asm' =>
          [
            't' =>
              [
                0 => 'text/x-asm',
              ],
          ],
        'aso' =>
          [
            't' =>
              [
                0 => 'application/vnd.accpac.simply.aso',
              ],
          ],
        'asp' =>
          [
            't' =>
              [
                0 => 'application/x-asp',
              ],
          ],
        'ass' =>
          [
            't' =>
              [
                0 => 'text/x-ssa',
                1 => 'audio/aac',
              ],
          ],
        'astc' =>
          [
            't' =>
              [
                0 => 'image/astc',
              ],
          ],
        'asx' =>
          [
            't' =>
              [
                0 => 'audio/x-ms-asx',
              ],
          ],
        'atc' =>
          [
            't' =>
              [
                0 => 'application/vnd.acucorp',
              ],
          ],
        'atom' =>
          [
            't' =>
              [
                0 => 'application/atom+xml',
              ],
          ],
        'atomcat' =>
          [
            't' =>
              [
                0 => 'application/atomcat+xml',
              ],
          ],
        'atomsvc' =>
          [
            't' =>
              [
                0 => 'application/atomsvc+xml',
              ],
          ],
        'atx' =>
          [
            't' =>
              [
                0 => 'application/vnd.antix.game-component',
              ],
          ],
        'au' =>
          [
            't' =>
              [
                0 => 'audio/basic',
              ],
          ],
        'automount' =>
          [
            't' =>
              [
                0 => 'text/x-systemd-unit',
              ],
          ],
        'avf' =>
          [
            't' =>
              [
                0 => 'video/vnd.avi',
              ],
          ],
        'avi' =>
          [
            't' =>
              [
                0 => 'video/vnd.avi',
              ],
          ],
        'avif' =>
          [
            't' =>
              [
                0 => 'image/avif',
              ],
          ],
        'avifs' =>
          [
            't' =>
              [
                0 => 'image/avif',
              ],
          ],
        'aw' =>
          [
            't' =>
              [
                0 => 'application/applixware',
                1 => 'application/x-applix-word',
              ],
          ],
        'awb' =>
          [
            't' =>
              [
                0 => 'audio/amr-wb',
              ],
          ],
        'awk' =>
          [
            't' =>
              [
                0 => 'application/x-awk',
              ],
          ],
        'axa' =>
          [
            't' =>
              [
                0 => 'audio/annodex',
              ],
          ],
        'axv' =>
          [
            't' =>
              [
                0 => 'video/annodex',
              ],
          ],
        'azf' =>
          [
            't' =>
              [
                0 => 'application/vnd.airzip.filesecure.azf',
              ],
          ],
        'azs' =>
          [
            't' =>
              [
                0 => 'application/vnd.airzip.filesecure.azs',
              ],
          ],
        'azw' =>
          [
            't' =>
              [
                0 => 'application/vnd.amazon.ebook',
              ],
          ],
        'azw3' =>
          [
            't' =>
              [
                0 => 'application/vnd.amazon.mobi8-ebook',
              ],
          ],
        'bak' =>
          [
            't' =>
              [
                0 => 'application/x-trash',
              ],
          ],
        'bas' =>
          [
            't' =>
              [
                0 => 'text/x-basic',
              ],
          ],
        'bat' =>
          [
            't' =>
              [
                0 => 'application/x-msdownload',
                1 => 'application/x-bat',
              ],
          ],
        'bcpio' =>
          [
            't' =>
              [
                0 => 'application/x-bcpio',
              ],
          ],
        'bdf' =>
          [
            't' =>
              [
                0 => 'application/x-font-bdf',
              ],
          ],
        'bdm' =>
          [
            't' =>
              [
                0 => 'application/vnd.syncml.dm+wbxml',
                1 => 'video/mp2t',
              ],
          ],
        'bdmv' =>
          [
            't' =>
              [
                0 => 'video/mp2t',
              ],
          ],
        'bed' =>
          [
            't' =>
              [
                0 => 'application/vnd.realvnc.bed',
              ],
          ],
        'bh2' =>
          [
            't' =>
              [
                0 => 'application/vnd.fujitsu.oasysprs',
              ],
          ],
        'bib' =>
          [
            't' =>
              [
                0 => 'text/x-bibtex',
              ],
          ],
        'bik' =>
          [
            't' =>
              [
                0 => 'video/vnd.radgamettools.bink',
              ],
          ],
        'bin' =>
          [
            't' =>
              [
                0 => 'application/octet-stream',
              ],
          ],
        'bk2' =>
          [
            't' =>
              [
                0 => 'video/vnd.radgamettools.bink',
              ],
          ],
        'blb' =>
          [
            't' =>
              [
                0 => 'application/x-blorb',
              ],
          ],
        'blend' =>
          [
            't' =>
              [
                0 => 'application/x-blender',
              ],
          ],
        'blender' =>
          [
            't' =>
              [
                0 => 'application/x-blender',
              ],
          ],
        'blorb' =>
          [
            't' =>
              [
                0 => 'application/x-blorb',
              ],
          ],
        'blp' =>
          [
            't' =>
              [
                0 => 'text/x-blueprint',
              ],
          ],
        'bmi' =>
          [
            't' =>
              [
                0 => 'application/vnd.bmi',
              ],
          ],
        'bmp' =>
          [
            't' =>
              [
                0 => 'image/bmp',
              ],
          ],
        'book' =>
          [
            't' =>
              [
                0 => 'application/vnd.framemaker',
              ],
          ],
        'box' =>
          [
            't' =>
              [
                0 => 'application/vnd.previewsystems.box',
              ],
          ],
        'boz' =>
          [
            't' =>
              [
                0 => 'application/x-bzip2',
              ],
          ],
        'bpk' =>
          [
            't' =>
              [
                0 => 'application/octet-stream',
              ],
          ],
        'bps' =>
          [
            't' =>
              [
                0 => 'application/x-bps-patch',
              ],
          ],
        'brk' =>
          [
            't' =>
              [
                0 => 'chemical/x-pdb',
              ],
          ],
        'bsdiff' =>
          [
            't' =>
              [
                0 => 'application/x-bsdiff',
              ],
          ],
        'btif' =>
          [
            't' =>
              [
                0 => 'image/prs.btif',
              ],
          ],
        'bz' =>
          [
            't' =>
              [
                0 => 'application/x-bzip1',
              ],
          ],
        'bz2' =>
          [
            't' =>
              [
                0 => 'application/x-bzip2',
              ],
          ],
        'bz3' =>
          [
            't' =>
              [
                0 => 'application/x-bzip3',
              ],
          ],
        'c' =>
          [
            't' =>
              [
                0 => 'text/x-c++src',
                1 => 'text/x-csrc',
              ],
          ],
        'c++' =>
          [
            't' =>
              [
                0 => 'text/x-c++src',
              ],
          ],
        'c11amc' =>
          [
            't' =>
              [
                0 => 'application/vnd.cluetrust.cartomobile-config',
              ],
          ],
        'c11amz' =>
          [
            't' =>
              [
                0 => 'application/vnd.cluetrust.cartomobile-config-pkg',
              ],
          ],
        'c4d' =>
          [
            't' =>
              [
                0 => 'application/vnd.clonk.c4group',
              ],
          ],
        'c4f' =>
          [
            't' =>
              [
                0 => 'application/vnd.clonk.c4group',
              ],
          ],
        'c4g' =>
          [
            't' =>
              [
                0 => 'application/vnd.clonk.c4group',
              ],
          ],
        'c4p' =>
          [
            't' =>
              [
                0 => 'application/vnd.clonk.c4group',
              ],
          ],
        'c4u' =>
          [
            't' =>
              [
                0 => 'application/vnd.clonk.c4group',
              ],
          ],
        'cab' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-cab-compressed',
              ],
          ],
        'caf' =>
          [
            't' =>
              [
                0 => 'audio/x-caf',
              ],
          ],
        'cap' =>
          [
            't' =>
              [
                0 => 'application/vnd.tcpdump.pcap',
              ],
          ],
        'car' =>
          [
            't' =>
              [
                0 => 'application/vnd.curl.car',
              ],
          ],
        'cat' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-pki.seccat',
              ],
          ],
        'cb7' =>
          [
            't' =>
              [
                0 => 'application/x-cb7',
              ],
          ],
        'cba' =>
          [
            't' =>
              [
                0 => 'application/vnd.comicbook-rar',
              ],
          ],
        'cbl' =>
          [
            't' =>
              [
                0 => 'text/x-cobol',
              ],
          ],
        'cbor' =>
          [
            't' =>
              [
                0 => 'application/cbor',
              ],
          ],
        'cbr' =>
          [
            't' =>
              [
                0 => 'application/vnd.comicbook-rar',
              ],
          ],
        'cbt' =>
          [
            't' =>
              [
                0 => 'application/x-cbt',
              ],
          ],
        'cbz' =>
          [
            't' =>
              [
                0 => 'application/vnd.comicbook+zip',
              ],
          ],
        'cc' =>
          [
            't' =>
              [
                0 => 'text/x-c++src',
              ],
          ],
        'cci' =>
          [
            't' =>
              [
                0 => 'application/x-nintendo-3ds-rom',
              ],
          ],
        'ccmx' =>
          [
            't' =>
              [
                0 => 'application/x-ccmx',
              ],
          ],
        'cct' =>
          [
            't' =>
              [
                0 => 'application/x-director',
              ],
          ],
        'ccxml' =>
          [
            't' =>
              [
                0 => 'application/ccxml+xml',
              ],
          ],
        'cdbcmsg' =>
          [
            't' =>
              [
                0 => 'application/vnd.contact.cmsg',
              ],
          ],
        'cdf' =>
          [
            't' =>
              [
                0 => 'application/x-netcdf',
              ],
          ],
        'cdi' =>
          [
            't' =>
              [
                0 => 'application/x-discjuggler-cd-image',
              ],
          ],
        'cdkey' =>
          [
            't' =>
              [
                0 => 'application/vnd.mediastation.cdkey',
              ],
          ],
        'cdmia' =>
          [
            't' =>
              [
                0 => 'application/cdmi-capability',
              ],
          ],
        'cdmic' =>
          [
            't' =>
              [
                0 => 'application/cdmi-container',
              ],
          ],
        'cdmid' =>
          [
            't' =>
              [
                0 => 'application/cdmi-domain',
              ],
          ],
        'cdmio' =>
          [
            't' =>
              [
                0 => 'application/cdmi-object',
              ],
          ],
        'cdmiq' =>
          [
            't' =>
              [
                0 => 'application/cdmi-queue',
              ],
          ],
        'cdr' =>
          [
            't' =>
              [
                0 => 'application/vnd.corel-draw',
              ],
          ],
        'cdx' =>
          [
            't' =>
              [
                0 => 'chemical/x-cdx',
              ],
          ],
        'cdxml' =>
          [
            't' =>
              [
                0 => 'application/vnd.chemdraw+xml',
              ],
          ],
        'cdy' =>
          [
            't' =>
              [
                0 => 'application/vnd.cinderella',
              ],
          ],
        'cel' =>
          [
            't' =>
              [
                0 => 'image/x-kiss-cel',
              ],
          ],
        'cer' =>
          [
            't' =>
              [
                0 => 'application/pkix-cert',
              ],
          ],
        'cert' =>
          [
            't' =>
              [
                0 => 'application/x-x509-ca-cert',
              ],
          ],
        'cfs' =>
          [
            't' =>
              [
                0 => 'application/x-cfs-compressed',
              ],
          ],
        'cgb' =>
          [
            't' =>
              [
                0 => 'application/x-gameboy-color-rom',
              ],
          ],
        'cgm' =>
          [
            't' =>
              [
                0 => 'image/cgm',
              ],
          ],
        'chat' =>
          [
            't' =>
              [
                0 => 'application/x-chat',
              ],
          ],
        'chd' =>
          [
            't' =>
              [
                0 => 'application/x-mame-chd',
              ],
          ],
        'chm' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-htmlhelp',
              ],
          ],
        'chrt' =>
          [
            't' =>
              [
                0 => 'application/vnd.kde.kchart',
                1 => 'application/x-kchart',
              ],
          ],
        'cif' =>
          [
            't' =>
              [
                0 => 'chemical/x-cif',
              ],
          ],
        'cii' =>
          [
            't' =>
              [
                0 => 'application/vnd.anser-web-certificate-issue-initiation',
              ],
          ],
        'cil' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-artgalry',
              ],
          ],
        'cl' =>
          [
            't' =>
              [
                0 => 'text/x-opencl-src',
              ],
          ],
        'cla' =>
          [
            't' =>
              [
                0 => 'application/vnd.claymore',
              ],
          ],
        'class' =>
          [
            't' =>
              [
                0 => 'application/x-java',
              ],
          ],
        'clkk' =>
          [
            't' =>
              [
                0 => 'application/vnd.crick.clicker.keyboard',
              ],
          ],
        'clkp' =>
          [
            't' =>
              [
                0 => 'application/vnd.crick.clicker.palette',
              ],
          ],
        'clkt' =>
          [
            't' =>
              [
                0 => 'application/vnd.crick.clicker.template',
              ],
          ],
        'clkw' =>
          [
            't' =>
              [
                0 => 'application/vnd.crick.clicker.wordbank',
              ],
          ],
        'clkx' =>
          [
            't' =>
              [
                0 => 'application/vnd.crick.clicker',
              ],
          ],
        'clp' =>
          [
            't' =>
              [
                0 => 'application/x-msclip',
              ],
          ],
        'clpi' =>
          [
            't' =>
              [
                0 => 'video/mp2t',
              ],
          ],
        'cls' =>
          [
            't' =>
              [
                0 => 'text/x-tex',
              ],
          ],
        'cmake' =>
          [
            't' =>
              [
                0 => 'text/x-cmake',
              ],
          ],
        'cmc' =>
          [
            't' =>
              [
                0 => 'application/vnd.cosmocaller',
              ],
          ],
        'cmdf' =>
          [
            't' =>
              [
                0 => 'chemical/x-cmdf',
              ],
          ],
        'cml' =>
          [
            't' =>
              [
                0 => 'chemical/x-cml',
              ],
          ],
        'cmp' =>
          [
            't' =>
              [
                0 => 'application/vnd.yellowriver-custom-menu',
              ],
          ],
        'cmx' =>
          [
            't' =>
              [
                0 => 'image/x-cmx',
              ],
          ],
        'cob' =>
          [
            't' =>
              [
                0 => 'text/x-cobol',
              ],
          ],
        'cod' =>
          [
            't' =>
              [
                0 => 'application/vnd.rim.cod',
              ],
          ],
        'coffee' =>
          [
            't' =>
              [
                0 => 'application/vnd.coffeescript',
              ],
          ],
        'com' =>
          [
            't' =>
              [
                0 => 'application/x-msdownload',
              ],
          ],
        'conf' =>
          [
            't' =>
              [
                0 => 'text/plain',
              ],
          ],
        'cpi' =>
          [
            't' =>
              [
                0 => 'video/mp2t',
              ],
          ],
        'cpio' =>
          [
            't' =>
              [
                0 => 'application/x-cpio',
              ],
          ],
        'cpio.gz' =>
          [
            't' =>
              [
                0 => 'application/x-cpio-compressed',
              ],
          ],
        'cpl' =>
          [
            't' =>
              [
                0 => 'application/x-msdownload',
                1 => 'application/x-ms-ne-executable',
                2 => 'application/vnd.microsoft.portable-executable',
              ],
          ],
        'cpp' =>
          [
            't' =>
              [
                0 => 'text/x-c++src',
              ],
          ],
        'cpt' =>
          [
            't' =>
              [
                0 => 'application/mac-compactpro',
              ],
          ],
        'cr' =>
          [
            't' =>
              [
                0 => 'text/x-crystal',
              ],
          ],
        'cr2' =>
          [
            't' =>
              [
                0 => 'image/x-canon-cr2',
              ],
          ],
        'cr3' =>
          [
            't' =>
              [
                0 => 'image/x-canon-cr3',
              ],
          ],
        'crd' =>
          [
            't' =>
              [
                0 => 'application/x-mscardfile',
              ],
          ],
        'crdownload' =>
          [
            't' =>
              [
                0 => 'application/x-partial-download',
              ],
          ],
        'crl' =>
          [
            't' =>
              [
                0 => 'application/pkix-crl',
              ],
          ],
        'crt' =>
          [
            't' =>
              [
                0 => 'application/x-x509-ca-cert',
              ],
          ],
        'crw' =>
          [
            't' =>
              [
                0 => 'image/x-canon-crw',
              ],
          ],
        'cryptonote' =>
          [
            't' =>
              [
                0 => 'application/vnd.rig.cryptonote',
              ],
          ],
        'cs' =>
          [
            't' =>
              [
                0 => 'text/x-csharp',
              ],
          ],
        'csh' =>
          [
            't' =>
              [
                0 => 'application/x-csh',
              ],
          ],
        'csml' =>
          [
            't' =>
              [
                0 => 'chemical/x-csml',
              ],
          ],
        'cso' =>
          [
            't' =>
              [
                0 => 'application/x-compressed-iso',
              ],
          ],
        'csp' =>
          [
            't' =>
              [
                0 => 'application/vnd.commonspace',
              ],
          ],
        'css' =>
          [
            't' =>
              [
                0 => 'text/css',
              ],
          ],
        'cst' =>
          [
            't' =>
              [
                0 => 'application/x-director',
              ],
          ],
        'csv' =>
          [
            't' =>
              [
                0 => 'text/csv',
              ],
          ],
        'csvs' =>
          [
            't' =>
              [
                0 => 'text/csv-schema',
              ],
          ],
        'cu' =>
          [
            't' =>
              [
                0 => 'application/cu-seeme',
              ],
          ],
        'cue' =>
          [
            't' =>
              [
                0 => 'application/x-cue',
              ],
          ],
        'cur' =>
          [
            't' =>
              [
                0 => 'image/x-win-bitmap',
              ],
          ],
        'curl' =>
          [
            't' =>
              [
                0 => 'text/vnd.curl',
              ],
          ],
        'cwk' =>
          [
            't' =>
              [
                0 => 'application/x-appleworks-document',
              ],
          ],
        'cww' =>
          [
            't' =>
              [
                0 => 'application/prs.cww',
              ],
          ],
        'cxt' =>
          [
            't' =>
              [
                0 => 'application/x-director',
              ],
          ],
        'cxx' =>
          [
            't' =>
              [
                0 => 'text/x-c++src',
              ],
          ],
        'd' =>
          [
            't' =>
              [
                0 => 'text/x-dsrc',
              ],
          ],
        'dae' =>
          [
            't' =>
              [
                0 => 'model/vnd.collada+xml',
              ],
          ],
        'daf' =>
          [
            't' =>
              [
                0 => 'application/vnd.mobius.daf',
              ],
          ],
        'dar' =>
          [
            't' =>
              [
                0 => 'application/x-dar',
              ],
          ],
        'dart' =>
          [
            't' =>
              [
                0 => 'application/vnd.dart',
              ],
          ],
        'dataless' =>
          [
            't' =>
              [
                0 => 'application/vnd.fdsn.seed',
              ],
          ],
        'davmount' =>
          [
            't' =>
              [
                0 => 'application/davmount+xml',
              ],
          ],
        'dbf' =>
          [
            't' =>
              [
                0 => 'application/vnd.dbf',
              ],
          ],
        'dbk' =>
          [
            't' =>
              [
                0 => 'application/docbook+xml',
              ],
          ],
        'dcl' =>
          [
            't' =>
              [
                0 => 'text/x-dcl',
              ],
          ],
        'dcm' =>
          [
            't' =>
              [
                0 => 'application/dicom',
              ],
          ],
        'dcr' =>
          [
            't' =>
              [
                0 => 'application/x-director',
                1 => 'image/x-kodak-dcr',
              ],
          ],
        'dcurl' =>
          [
            't' =>
              [
                0 => 'text/vnd.curl.dcurl',
              ],
          ],
        'dd2' =>
          [
            't' =>
              [
                0 => 'application/vnd.oma.dd2+xml',
              ],
          ],
        'ddd' =>
          [
            't' =>
              [
                0 => 'application/vnd.fujixerox.ddd',
              ],
          ],
        'dds' =>
          [
            't' =>
              [
                0 => 'image/x-dds',
              ],
          ],
        'deb' =>
          [
            't' =>
              [
                0 => 'application/vnd.debian.binary-package',
              ],
          ],
        'def' =>
          [
            't' =>
              [
                0 => 'text/plain',
              ],
          ],
        'deploy' =>
          [
            't' =>
              [
                0 => 'application/octet-stream',
              ],
          ],
        'der' =>
          [
            't' =>
              [
                0 => 'application/x-x509-ca-cert',
              ],
          ],
        'desktop' =>
          [
            't' =>
              [
                0 => 'application/x-desktop',
              ],
          ],
        'device' =>
          [
            't' =>
              [
                0 => 'text/x-systemd-unit',
              ],
          ],
        'dfac' =>
          [
            't' =>
              [
                0 => 'application/vnd.dreamfactory',
              ],
          ],
        'dff' =>
          [
            't' =>
              [
                0 => 'audio/x-dff',
              ],
          ],
        'dgc' =>
          [
            't' =>
              [
                0 => 'application/x-dgc-compressed',
              ],
          ],
        'di' =>
          [
            't' =>
              [
                0 => 'text/x-dsrc',
              ],
          ],
        'dia' =>
          [
            't' =>
              [
                0 => 'application/x-dia-diagram',
              ],
          ],
        'dib' =>
          [
            't' =>
              [
                0 => 'image/bmp',
              ],
          ],
        'dic' =>
          [
            't' =>
              [
                0 => 'text/x-csrc',
              ],
          ],
        'diff' =>
          [
            't' =>
              [
                0 => 'text/x-patch',
              ],
          ],
        'dir' =>
          [
            't' =>
              [
                0 => 'application/x-director',
              ],
          ],
        'dis' =>
          [
            't' =>
              [
                0 => 'application/vnd.mobius.dis',
              ],
          ],
        'dist' =>
          [
            't' =>
              [
                0 => 'application/octet-stream',
              ],
          ],
        'distz' =>
          [
            't' =>
              [
                0 => 'application/octet-stream',
              ],
          ],
        'divx' =>
          [
            't' =>
              [
                0 => 'video/vnd.avi',
              ],
          ],
        'djv' =>
          [
            't' =>
              [
                0 => 'image/vnd.djvu',
                1 => 'image/vnd.djvu+multipage',
              ],
          ],
        'djvu' =>
          [
            't' =>
              [
                0 => 'image/vnd.djvu',
                1 => 'image/vnd.djvu+multipage',
              ],
          ],
        'dll' =>
          [
            't' =>
              [
                0 => 'application/x-msdownload',
                1 => 'application/x-ms-ne-executable',
                2 => 'application/vnd.microsoft.portable-executable',
              ],
          ],
        'dmg' =>
          [
            't' =>
              [
                0 => 'application/x-apple-diskimage',
              ],
          ],
        'dmp' =>
          [
            't' =>
              [
                0 => 'application/vnd.tcpdump.pcap',
              ],
          ],
        'dms' =>
          [
            't' =>
              [
                0 => 'application/octet-stream',
              ],
          ],
        'dna' =>
          [
            't' =>
              [
                0 => 'application/vnd.dna',
              ],
          ],
        'dng' =>
          [
            't' =>
              [
                0 => 'image/x-adobe-dng',
              ],
          ],
        'doc' =>
          [
            't' =>
              [
                0 => 'application/msword',
              ],
          ],
        'docbook' =>
          [
            't' =>
              [
                0 => 'application/docbook+xml',
              ],
          ],
        'docm' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-word.document.macroenabled.12',
              ],
          ],
        'docx' =>
          [
            't' =>
              [
                0 => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
              ],
          ],
        'dot' =>
          [
            't' =>
              [
                0 => 'application/msword',
                1 => 'application/msword-template',
                2 => 'text/vnd.graphviz',
              ],
          ],
        'dotm' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-word.template.macroenabled.12',
              ],
          ],
        'dotx' =>
          [
            't' =>
              [
                0 => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
              ],
          ],
        'dp' =>
          [
            't' =>
              [
                0 => 'application/vnd.osgi.dp',
              ],
          ],
        'dpg' =>
          [
            't' =>
              [
                0 => 'application/vnd.dpgraph',
              ],
          ],
        'dra' =>
          [
            't' =>
              [
                0 => 'audio/vnd.dra',
              ],
          ],
        'drl' =>
          [
            't' =>
              [
                0 => 'application/x-excellon',
              ],
          ],
        'drv' =>
          [
            't' =>
              [
                0 => 'application/x-msdownload',
                1 => 'application/x-ms-ne-executable',
                2 => 'application/vnd.microsoft.portable-executable',
              ],
          ],
        'dsc' =>
          [
            't' =>
              [
                0 => 'text/prs.lines.tag',
              ],
          ],
        'dsf' =>
          [
            't' =>
              [
                0 => 'audio/x-dsf',
              ],
          ],
        'dsl' =>
          [
            't' =>
              [
                0 => 'text/x-dsl',
              ],
          ],
        'dssc' =>
          [
            't' =>
              [
                0 => 'application/dssc+der',
              ],
          ],
        'dtb' =>
          [
            't' =>
              [
                0 => 'application/x-dtbook+xml',
                1 => 'text/x-devicetree-binary',
              ],
          ],
        'dtd' =>
          [
            't' =>
              [
                0 => 'application/xml-dtd',
              ],
          ],
        'dts' =>
          [
            't' =>
              [
                0 => 'audio/vnd.dts',
                1 => 'text/x-devicetree-source',
              ],
          ],
        'dtshd' =>
          [
            't' =>
              [
                0 => 'audio/vnd.dts.hd',
              ],
          ],
        'dtsi' =>
          [
            't' =>
              [
                0 => 'text/x-devicetree-source',
              ],
          ],
        'dtx' =>
          [
            't' =>
              [
                0 => 'text/x-tex',
              ],
          ],
        'dump' =>
          [
            't' =>
              [
                0 => 'application/octet-stream',
              ],
          ],
        'dv' =>
          [
            't' =>
              [
                0 => 'video/dv',
              ],
          ],
        'dvb' =>
          [
            't' =>
              [
                0 => 'video/vnd.dvb.file',
              ],
          ],
        'dvi' =>
          [
            't' =>
              [
                0 => 'application/x-dvi',
              ],
          ],
        'dvi.bz2' =>
          [
            't' =>
              [
                0 => 'application/x-bzdvi',
              ],
          ],
        'dvi.gz' =>
          [
            't' =>
              [
                0 => 'application/x-gzdvi',
              ],
          ],
        'dwf' =>
          [
            't' =>
              [
                0 => 'model/vnd.dwf',
              ],
          ],
        'dwg' =>
          [
            't' =>
              [
                0 => 'image/vnd.dwg',
              ],
          ],
        'dxf' =>
          [
            't' =>
              [
                0 => 'image/vnd.dxf',
              ],
          ],
        'dxp' =>
          [
            't' =>
              [
                0 => 'application/vnd.spotfire.dxp',
              ],
          ],
        'dxr' =>
          [
            't' =>
              [
                0 => 'application/x-director',
              ],
          ],
        'e' =>
          [
            't' =>
              [
                0 => 'text/x-eiffel',
              ],
          ],
        'ecelp4800' =>
          [
            't' =>
              [
                0 => 'audio/vnd.nuera.ecelp4800',
              ],
          ],
        'ecelp7470' =>
          [
            't' =>
              [
                0 => 'audio/vnd.nuera.ecelp7470',
              ],
          ],
        'ecelp9600' =>
          [
            't' =>
              [
                0 => 'audio/vnd.nuera.ecelp9600',
              ],
          ],
        'ecma' =>
          [
            't' =>
              [
                0 => 'application/ecmascript',
              ],
          ],
        'edm' =>
          [
            't' =>
              [
                0 => 'application/vnd.novadigm.edm',
              ],
          ],
        'edx' =>
          [
            't' =>
              [
                0 => 'application/vnd.novadigm.edx',
              ],
          ],
        'efi' =>
          [
            't' =>
              [
                0 => 'application/vnd.microsoft.portable-executable',
              ],
          ],
        'efif' =>
          [
            't' =>
              [
                0 => 'application/vnd.picsel',
              ],
          ],
        'egon' =>
          [
            't' =>
              [
                0 => 'application/x-egon',
              ],
          ],
        'ei6' =>
          [
            't' =>
              [
                0 => 'application/vnd.pg.osasli',
              ],
          ],
        'eif' =>
          [
            't' =>
              [
                0 => 'text/x-eiffel',
              ],
          ],
        'el' =>
          [
            't' =>
              [
                0 => 'text/x-emacs-lisp',
              ],
          ],
        'elc' =>
          [
            't' =>
              [
                0 => 'application/octet-stream',
              ],
          ],
        'emf' =>
          [
            't' =>
              [
                0 => 'image/emf',
              ],
          ],
        'eml' =>
          [
            't' =>
              [
                0 => 'message/rfc822',
              ],
          ],
        'emma' =>
          [
            't' =>
              [
                0 => 'application/emma+xml',
              ],
          ],
        'emp' =>
          [
            't' =>
              [
                0 => 'application/vnd.emusic-emusic_package',
              ],
          ],
        'emz' =>
          [
            't' =>
              [
                0 => 'image/wmf',
              ],
          ],
        'ent' =>
          [
            't' =>
              [
                0 => 'application/xml-external-parsed-entity',
              ],
          ],
        'eol' =>
          [
            't' =>
              [
                0 => 'audio/vnd.digital-winds',
              ],
          ],
        'eot' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-fontobject',
              ],
          ],
        'eps' =>
          [
            't' =>
              [
                0 => 'application/postscript',
                1 => 'image/x-eps',
              ],
          ],
        'eps.bz2' =>
          [
            't' =>
              [
                0 => 'image/x-bzeps',
              ],
          ],
        'eps.gz' =>
          [
            't' =>
              [
                0 => 'image/x-gzeps',
              ],
          ],
        'epsf' =>
          [
            't' =>
              [
                0 => 'image/x-eps',
              ],
          ],
        'epsf.bz2' =>
          [
            't' =>
              [
                0 => 'image/x-bzeps',
              ],
          ],
        'epsf.gz' =>
          [
            't' =>
              [
                0 => 'image/x-gzeps',
              ],
          ],
        'epsi' =>
          [
            't' =>
              [
                0 => 'image/x-eps',
              ],
          ],
        'epsi.bz2' =>
          [
            't' =>
              [
                0 => 'image/x-bzeps',
              ],
          ],
        'epsi.gz' =>
          [
            't' =>
              [
                0 => 'image/x-gzeps',
              ],
          ],
        'epub' =>
          [
            't' =>
              [
                0 => 'application/epub+zip',
              ],
          ],
        'eris' =>
          [
            't' =>
              [
                0 => 'application/x-eris-link+cbor',
              ],
          ],
        'erl' =>
          [
            't' =>
              [
                0 => 'text/x-erlang',
              ],
          ],
        'es' =>
          [
            't' =>
              [
                0 => 'application/ecmascript',
              ],
          ],
        'es3' =>
          [
            't' =>
              [
                0 => 'application/vnd.eszigno3+xml',
              ],
          ],
        'esa' =>
          [
            't' =>
              [
                0 => 'application/vnd.osgi.subsystem',
              ],
          ],
        'escn' =>
          [
            't' =>
              [
                0 => 'application/x-godot-scene',
              ],
          ],
        'esf' =>
          [
            't' =>
              [
                0 => 'application/vnd.epson.esf',
              ],
          ],
        'et3' =>
          [
            't' =>
              [
                0 => 'application/vnd.eszigno3+xml',
              ],
          ],
        'etheme' =>
          [
            't' =>
              [
                0 => 'application/x-e-theme',
              ],
          ],
        'etx' =>
          [
            't' =>
              [
                0 => 'text/x-setext',
              ],
          ],
        'eva' =>
          [
            't' =>
              [
                0 => 'application/x-eva',
              ],
          ],
        'evy' =>
          [
            't' =>
              [
                0 => 'application/x-envoy',
              ],
          ],
        'ex' =>
          [
            't' =>
              [
                0 => 'text/x-elixir',
              ],
          ],
        'exe' =>
          [
            't' =>
              [
                0 => 'application/x-msdownload',
                1 => 'application/x-dosexec',
                2 => 'application/x-ms-ne-executable',
                3 => 'application/vnd.microsoft.portable-executable',
              ],
          ],
        'exi' =>
          [
            't' =>
              [
                0 => 'application/exi',
              ],
          ],
        'exr' =>
          [
            't' =>
              [
                0 => 'image/x-exr',
              ],
          ],
        'exs' =>
          [
            't' =>
              [
                0 => 'text/x-elixir',
              ],
          ],
        'ext' =>
          [
            't' =>
              [
                0 => 'application/vnd.novadigm.ext',
              ],
          ],
        'ez' =>
          [
            't' =>
              [
                0 => 'application/andrew-inset',
              ],
          ],
        'ez2' =>
          [
            't' =>
              [
                0 => 'application/vnd.ezpix-album',
              ],
          ],
        'ez3' =>
          [
            't' =>
              [
                0 => 'application/vnd.ezpix-package',
              ],
          ],
        'f' =>
          [
            't' =>
              [
                0 => 'text/x-fortran',
              ],
          ],
        'f4a' =>
          [
            't' =>
              [
                0 => 'audio/mp4',
              ],
          ],
        'f4b' =>
          [
            't' =>
              [
                0 => 'audio/x-m4b',
              ],
          ],
        'f4v' =>
          [
            't' =>
              [
                0 => 'video/x-f4v',
                1 => 'video/mp4',
              ],
          ],
        'f77' =>
          [
            't' =>
              [
                0 => 'text/x-fortran',
              ],
          ],
        'f90' =>
          [
            't' =>
              [
                0 => 'text/x-fortran',
              ],
          ],
        'f95' =>
          [
            't' =>
              [
                0 => 'text/x-fortran',
              ],
          ],
        'fasl' =>
          [
            't' =>
              [
                0 => 'text/x-common-lisp',
              ],
          ],
        'fb2' =>
          [
            't' =>
              [
                0 => 'application/x-fictionbook+xml',
              ],
          ],
        'fb2.zip' =>
          [
            't' =>
              [
                0 => 'application/x-zip-compressed-fb2',
              ],
          ],
        'fbs' =>
          [
            't' =>
              [
                0 => 'image/vnd.fastbidsheet',
              ],
          ],
        'fcdt' =>
          [
            't' =>
              [
                0 => 'application/vnd.adobe.formscentral.fcdt',
              ],
          ],
        'fcs' =>
          [
            't' =>
              [
                0 => 'application/vnd.isac.fcs',
              ],
          ],
        'fd' =>
          [
            't' =>
              [
                0 => 'application/x-raw-floppy-disk-image',
              ],
          ],
        'fdf' =>
          [
            't' =>
              [
                0 => 'application/vnd.fdf',
              ],
          ],
        'fds' =>
          [
            't' =>
              [
                0 => 'application/x-fds-disk',
              ],
          ],
        'fe_launch' =>
          [
            't' =>
              [
                0 => 'application/vnd.denovo.fcselayout-link',
              ],
          ],
        'feature' =>
          [
            't' =>
              [
                0 => 'text/x-gherkin',
              ],
          ],
        'fg5' =>
          [
            't' =>
              [
                0 => 'application/vnd.fujitsu.oasysgp',
              ],
          ],
        'fgd' =>
          [
            't' =>
              [
                0 => 'application/x-director',
              ],
          ],
        'fh' =>
          [
            't' =>
              [
                0 => 'image/x-freehand',
              ],
          ],
        'fh4' =>
          [
            't' =>
              [
                0 => 'image/x-freehand',
              ],
          ],
        'fh5' =>
          [
            't' =>
              [
                0 => 'image/x-freehand',
              ],
          ],
        'fh7' =>
          [
            't' =>
              [
                0 => 'image/x-freehand',
              ],
          ],
        'fhc' =>
          [
            't' =>
              [
                0 => 'image/x-freehand',
              ],
          ],
        'fig' =>
          [
            't' =>
              [
                0 => 'application/x-xfig',
                1 => 'image/x-xfig',
              ],
          ],
        'fish' =>
          [
            't' =>
              [
                0 => 'application/x-fishscript',
              ],
          ],
        'fit' =>
          [
            't' =>
              [
                0 => 'application/fits',
              ],
          ],
        'fits' =>
          [
            't' =>
              [
                0 => 'application/fits',
              ],
          ],
        'fl' =>
          [
            't' =>
              [
                0 => 'application/x-fluid',
              ],
          ],
        'flac' =>
          [
            't' =>
              [
                0 => 'audio/flac',
              ],
          ],
        'flatpak' =>
          [
            't' =>
              [
                0 => 'application/vnd.flatpak',
              ],
          ],
        'flatpakref' =>
          [
            't' =>
              [
                0 => 'application/vnd.flatpak.ref',
              ],
          ],
        'flatpakrepo' =>
          [
            't' =>
              [
                0 => 'application/vnd.flatpak.repo',
              ],
          ],
        'flc' =>
          [
            't' =>
              [
                0 => 'video/x-flic',
              ],
          ],
        'fli' =>
          [
            't' =>
              [
                0 => 'video/x-flic',
              ],
          ],
        'flo' =>
          [
            't' =>
              [
                0 => 'application/vnd.micrografx.flo',
              ],
          ],
        'flv' =>
          [
            't' =>
              [
                0 => 'video/x-flv',
              ],
          ],
        'flw' =>
          [
            't' =>
              [
                0 => 'application/vnd.kde.kivio',
                1 => 'application/x-kivio',
              ],
          ],
        'flx' =>
          [
            't' =>
              [
                0 => 'text/vnd.fmi.flexstor',
              ],
          ],
        'fly' =>
          [
            't' =>
              [
                0 => 'text/vnd.fly',
              ],
          ],
        'fm' =>
          [
            't' =>
              [
                0 => 'application/vnd.framemaker',
              ],
          ],
        'fnc' =>
          [
            't' =>
              [
                0 => 'application/vnd.frogans.fnc',
              ],
          ],
        'fo' =>
          [
            't' =>
              [
                0 => 'text/x-xslfo',
              ],
          ],
        'fodg' =>
          [
            't' =>
              [
                0 => 'application/vnd.oasis.opendocument.graphics-flat-xml',
              ],
          ],
        'fodp' =>
          [
            't' =>
              [
                0 => 'application/vnd.oasis.opendocument.presentation-flat-xml',
              ],
          ],
        'fods' =>
          [
            't' =>
              [
                0 => 'application/vnd.oasis.opendocument.spreadsheet-flat-xml',
              ],
          ],
        'fodt' =>
          [
            't' =>
              [
                0 => 'application/vnd.oasis.opendocument.text-flat-xml',
              ],
          ],
        'for' =>
          [
            't' =>
              [
                0 => 'text/x-fortran',
              ],
          ],
        'fpx' =>
          [
            't' =>
              [
                0 => 'image/vnd.fpx',
              ],
          ],
        'frame' =>
          [
            't' =>
              [
                0 => 'application/vnd.framemaker',
              ],
          ],
        'fsc' =>
          [
            't' =>
              [
                0 => 'application/vnd.fsc.weblaunch',
              ],
          ],
        'fst' =>
          [
            't' =>
              [
                0 => 'image/vnd.fst',
              ],
          ],
        'ftc' =>
          [
            't' =>
              [
                0 => 'application/vnd.fluxtime.clip',
              ],
          ],
        'fti' =>
          [
            't' =>
              [
                0 => 'application/vnd.anser-web-funds-transfer-initiation',
              ],
          ],
        'fts' =>
          [
            't' =>
              [
                0 => 'application/fits',
              ],
          ],
        'fvt' =>
          [
            't' =>
              [
                0 => 'video/vnd.fvt',
              ],
          ],
        'fxm' =>
          [
            't' =>
              [
                0 => 'video/x-javafx',
              ],
          ],
        'fxp' =>
          [
            't' =>
              [
                0 => 'application/vnd.adobe.fxp',
              ],
          ],
        'fxpl' =>
          [
            't' =>
              [
                0 => 'application/vnd.adobe.fxp',
              ],
          ],
        'fzs' =>
          [
            't' =>
              [
                0 => 'application/vnd.fuzzysheet',
              ],
          ],
        'g2w' =>
          [
            't' =>
              [
                0 => 'application/vnd.geoplan',
              ],
          ],
        'g3' =>
          [
            't' =>
              [
                0 => 'image/g3fax',
              ],
          ],
        'g3w' =>
          [
            't' =>
              [
                0 => 'application/vnd.geospace',
              ],
          ],
        'gac' =>
          [
            't' =>
              [
                0 => 'application/vnd.groove-account',
              ],
          ],
        'gam' =>
          [
            't' =>
              [
                0 => 'application/x-tads',
              ],
          ],
        'gb' =>
          [
            't' =>
              [
                0 => 'application/x-gameboy-rom',
              ],
          ],
        'gba' =>
          [
            't' =>
              [
                0 => 'application/x-gba-rom',
              ],
          ],
        'gbc' =>
          [
            't' =>
              [
                0 => 'application/x-gameboy-color-rom',
              ],
          ],
        'gbr' =>
          [
            't' =>
              [
                0 => 'application/rpki-ghostbusters',
                1 => 'image/x-gimp-gbr',
                2 => 'application/vnd.gerber',
              ],
          ],
        'gbrjob' =>
          [
            't' =>
              [
                0 => 'application/x-gerber-job',
              ],
          ],
        'gca' =>
          [
            't' =>
              [
                0 => 'application/x-gca-compressed',
              ],
          ],
        'gcode' =>
          [
            't' =>
              [
                0 => 'text/x.gcode',
              ],
          ],
        'gcrd' =>
          [
            't' =>
              [
                0 => 'text/vcard',
              ],
          ],
        'gd' =>
          [
            't' =>
              [
                0 => 'application/x-gdscript',
              ],
          ],
        'gdi' =>
          [
            't' =>
              [
                0 => 'application/x-gd-rom-cue',
              ],
          ],
        'gdl' =>
          [
            't' =>
              [
                0 => 'model/vnd.gdl',
              ],
          ],
        'gdshader' =>
          [
            't' =>
              [
                0 => 'application/x-godot-shader',
              ],
          ],
        'ged' =>
          [
            't' =>
              [
                0 => 'text/vnd.familysearch.gedcom',
              ],
          ],
        'gedcom' =>
          [
            't' =>
              [
                0 => 'text/vnd.familysearch.gedcom',
              ],
          ],
        'gem' =>
          [
            't' =>
              [
                0 => 'application/x-tar',
              ],
          ],
        'gen' =>
          [
            't' =>
              [
                0 => 'application/x-genesis-rom',
              ],
          ],
        'geo' =>
          [
            't' =>
              [
                0 => 'application/vnd.dynageo',
              ],
          ],
        'geo.json' =>
          [
            't' =>
              [
                0 => 'application/geo+json',
              ],
          ],
        'geojson' =>
          [
            't' =>
              [
                0 => 'application/geo+json',
              ],
          ],
        'gex' =>
          [
            't' =>
              [
                0 => 'application/vnd.geometry-explorer',
              ],
          ],
        'gf' =>
          [
            't' =>
              [
                0 => 'application/x-tex-gf',
              ],
          ],
        'gg' =>
          [
            't' =>
              [
                0 => 'application/x-gamegear-rom',
              ],
          ],
        'ggb' =>
          [
            't' =>
              [
                0 => 'application/vnd.geogebra.file',
              ],
          ],
        'ggs' =>
          [
            't' =>
              [
                0 => 'application/vnd.geogebra.slides',
              ],
          ],
        'ggt' =>
          [
            't' =>
              [
                0 => 'application/vnd.geogebra.tool',
              ],
          ],
        'ghf' =>
          [
            't' =>
              [
                0 => 'application/vnd.groove-help',
              ],
          ],
        'gif' =>
          [
            't' =>
              [
                0 => 'image/gif',
              ],
          ],
        'gih' =>
          [
            't' =>
              [
                0 => 'image/x-gimp-gih',
              ],
          ],
        'gim' =>
          [
            't' =>
              [
                0 => 'application/vnd.groove-identity-message',
              ],
          ],
        'glade' =>
          [
            't' =>
              [
                0 => 'application/x-glade',
              ],
          ],
        'glb' =>
          [
            't' =>
              [
                0 => 'model/gltf-binary',
              ],
          ],
        'gltf' =>
          [
            't' =>
              [
                0 => 'model/gltf+json',
              ],
          ],
        'gml' =>
          [
            't' =>
              [
                0 => 'application/gml+xml',
              ],
          ],
        'gmo' =>
          [
            't' =>
              [
                0 => 'application/x-gettext-translation',
              ],
          ],
        'gmx' =>
          [
            't' =>
              [
                0 => 'application/vnd.gmx',
              ],
          ],
        'gnc' =>
          [
            't' =>
              [
                0 => 'application/x-gnucash',
              ],
          ],
        'gnd' =>
          [
            't' =>
              [
                0 => 'application/gnunet-directory',
              ],
          ],
        'gnucash' =>
          [
            't' =>
              [
                0 => 'application/x-gnucash',
              ],
          ],
        'gnumeric' =>
          [
            't' =>
              [
                0 => 'application/x-gnumeric',
              ],
          ],
        'gnuplot' =>
          [
            't' =>
              [
                0 => 'application/x-gnuplot',
              ],
          ],
        'go' =>
          [
            't' =>
              [
                0 => 'text/x-go',
              ],
          ],
        'gp' =>
          [
            't' =>
              [
                0 => 'application/x-gnuplot',
              ],
          ],
        'gpg' =>
          [
            't' =>
              [
                0 => 'application/pgp-encrypted',
                1 => 'application/pgp-keys',
                2 => 'application/pgp-signature',
              ],
          ],
        'gph' =>
          [
            't' =>
              [
                0 => 'application/vnd.flographit',
              ],
          ],
        'gplt' =>
          [
            't' =>
              [
                0 => 'application/x-gnuplot',
              ],
          ],
        'gpx' =>
          [
            't' =>
              [
                0 => 'application/gpx+xml',
              ],
          ],
        'gqf' =>
          [
            't' =>
              [
                0 => 'application/vnd.grafeq',
              ],
          ],
        'gqs' =>
          [
            't' =>
              [
                0 => 'application/vnd.grafeq',
              ],
          ],
        'gra' =>
          [
            't' =>
              [
                0 => 'application/x-graphite',
              ],
          ],
        'gradle' =>
          [
            't' =>
              [
                0 => 'text/x-gradle',
              ],
          ],
        'gram' =>
          [
            't' =>
              [
                0 => 'application/srgs',
              ],
          ],
        'gramps' =>
          [
            't' =>
              [
                0 => 'application/x-gramps-xml',
              ],
          ],
        'gre' =>
          [
            't' =>
              [
                0 => 'application/vnd.geometry-explorer',
              ],
          ],
        'groovy' =>
          [
            't' =>
              [
                0 => 'text/x-groovy',
              ],
          ],
        'grv' =>
          [
            't' =>
              [
                0 => 'application/vnd.groove-injector',
              ],
          ],
        'grxml' =>
          [
            't' =>
              [
                0 => 'application/srgs+xml',
              ],
          ],
        'gs' =>
          [
            't' =>
              [
                0 => 'text/x-genie',
              ],
          ],
        'gsf' =>
          [
            't' =>
              [
                0 => 'application/x-font-ghostscript',
                1 => 'application/x-font-type1',
              ],
          ],
        'gsh' =>
          [
            't' =>
              [
                0 => 'text/x-groovy',
              ],
          ],
        'gsm' =>
          [
            't' =>
              [
                0 => 'audio/x-gsm',
              ],
          ],
        'gtar' =>
          [
            't' =>
              [
                0 => 'application/x-tar',
              ],
          ],
        'gtm' =>
          [
            't' =>
              [
                0 => 'application/vnd.groove-tool-message',
              ],
          ],
        'gtw' =>
          [
            't' =>
              [
                0 => 'model/vnd.gtw',
              ],
          ],
        'gv' =>
          [
            't' =>
              [
                0 => 'text/vnd.graphviz',
              ],
          ],
        'gvp' =>
          [
            't' =>
              [
                0 => 'text/x-google-video-pointer',
              ],
          ],
        'gvy' =>
          [
            't' =>
              [
                0 => 'text/x-groovy',
              ],
          ],
        'gx' =>
          [
            't' =>
              [
                0 => 'text/x-gcode-gx',
              ],
          ],
        'gxf' =>
          [
            't' =>
              [
                0 => 'application/gxf',
              ],
          ],
        'gxt' =>
          [
            't' =>
              [
                0 => 'application/vnd.geonext',
              ],
          ],
        'gy' =>
          [
            't' =>
              [
                0 => 'text/x-groovy',
              ],
          ],
        'gz' =>
          [
            't' =>
              [
                0 => 'application/gzip',
              ],
          ],
        'h' =>
          [
            't' =>
              [
                0 => 'text/x-chdr',
              ],
          ],
        'h++' =>
          [
            't' =>
              [
                0 => 'text/x-c++hdr',
              ],
          ],
        'h261' =>
          [
            't' =>
              [
                0 => 'video/h261',
              ],
          ],
        'h263' =>
          [
            't' =>
              [
                0 => 'video/h263',
              ],
          ],
        'h264' =>
          [
            't' =>
              [
                0 => 'video/h264',
              ],
          ],
        'h4' =>
          [
            't' =>
              [
                0 => 'application/x-hdf',
              ],
          ],
        'h5' =>
          [
            't' =>
              [
                0 => 'application/x-hdf',
              ],
          ],
        'hal' =>
          [
            't' =>
              [
                0 => 'application/vnd.hal+xml',
              ],
          ],
        'hbci' =>
          [
            't' =>
              [
                0 => 'application/vnd.hbci',
              ],
          ],
        'hdf' =>
          [
            't' =>
              [
                0 => 'application/x-hdf',
              ],
          ],
        'hdf4' =>
          [
            't' =>
              [
                0 => 'application/x-hdf',
              ],
          ],
        'hdf5' =>
          [
            't' =>
              [
                0 => 'application/x-hdf',
              ],
          ],
        'hdp' =>
          [
            't' =>
              [
                0 => 'image/jxr',
              ],
          ],
        'heic' =>
          [
            't' =>
              [
                0 => 'image/heif',
              ],
          ],
        'heif' =>
          [
            't' =>
              [
                0 => 'image/heif',
              ],
          ],
        'hej2' =>
          [
            't' =>
              [
                0 => 'image/hej2k',
              ],
          ],
        'hfe' =>
          [
            't' =>
              [
                0 => 'application/x-hfe-floppy-image',
              ],
          ],
        'hh' =>
          [
            't' =>
              [
                0 => 'text/x-c++hdr',
              ],
          ],
        'hif' =>
          [
            't' =>
              [
                0 => 'image/heif',
              ],
          ],
        'hlp' =>
          [
            't' =>
              [
                0 => 'application/winhlp',
              ],
          ],
        'hp' =>
          [
            't' =>
              [
                0 => 'text/x-c++hdr',
              ],
          ],
        'hpgl' =>
          [
            't' =>
              [
                0 => 'application/vnd.hp-hpgl',
              ],
          ],
        'hpid' =>
          [
            't' =>
              [
                0 => 'application/vnd.hp-hpid',
              ],
          ],
        'hpp' =>
          [
            't' =>
              [
                0 => 'text/x-c++hdr',
              ],
          ],
        'hps' =>
          [
            't' =>
              [
                0 => 'application/vnd.hp-hps',
              ],
          ],
        'hqx' =>
          [
            't' =>
              [
                0 => 'application/mac-binhex40',
              ],
          ],
        'hs' =>
          [
            't' =>
              [
                0 => 'text/x-haskell',
              ],
          ],
        'hta' =>
          [
            't' =>
              [
                0 => 'application/hta',
              ],
          ],
        'htc' =>
          [
            't' =>
              [
                0 => 'text/x-component',
              ],
          ],
        'htke' =>
          [
            't' =>
              [
                0 => 'application/vnd.kenameaapp',
              ],
          ],
        'htm' =>
          [
            't' =>
              [
                0 => 'text/html',
                1 => 'application/xhtml+xml',
              ],
          ],
        'html' =>
          [
            't' =>
              [
                0 => 'text/html',
                1 => 'application/xhtml+xml',
              ],
          ],
        'hvd' =>
          [
            't' =>
              [
                0 => 'application/vnd.yamaha.hv-dic',
              ],
          ],
        'hvp' =>
          [
            't' =>
              [
                0 => 'application/vnd.yamaha.hv-voice',
              ],
          ],
        'hvs' =>
          [
            't' =>
              [
                0 => 'application/vnd.yamaha.hv-script',
              ],
          ],
        'hwp' =>
          [
            't' =>
              [
                0 => 'application/x-hwp',
              ],
          ],
        'hwt' =>
          [
            't' =>
              [
                0 => 'application/x-hwt',
              ],
          ],
        'hxx' =>
          [
            't' =>
              [
                0 => 'text/x-c++hdr',
              ],
          ],
        'i2g' =>
          [
            't' =>
              [
                0 => 'application/vnd.intergeo',
              ],
          ],
        'ica' =>
          [
            't' =>
              [
                0 => 'application/x-ica',
              ],
          ],
        'icalendar' =>
          [
            't' =>
              [
                0 => 'text/calendar',
              ],
          ],
        'icb' =>
          [
            't' =>
              [
                0 => 'image/x-tga',
              ],
          ],
        'icc' =>
          [
            't' =>
              [
                0 => 'application/vnd.iccprofile',
              ],
          ],
        'ice' =>
          [
            't' =>
              [
                0 => 'x-conference/x-cooltalk',
              ],
          ],
        'icm' =>
          [
            't' =>
              [
                0 => 'application/vnd.iccprofile',
              ],
          ],
        'icns' =>
          [
            't' =>
              [
                0 => 'image/x-icns',
              ],
          ],
        'ico' =>
          [
            't' =>
              [
                0 => 'image/vnd.microsoft.icon',
              ],
          ],
        'ics' =>
          [
            't' =>
              [
                0 => 'text/calendar',
              ],
          ],
        'idl' =>
          [
            't' =>
              [
                0 => 'text/x-idl',
              ],
          ],
        'ief' =>
          [
            't' =>
              [
                0 => 'image/ief',
              ],
          ],
        'ifb' =>
          [
            't' =>
              [
                0 => 'text/calendar',
              ],
          ],
        'iff' =>
          [
            't' =>
              [
                0 => 'image/x-ilbm',
              ],
          ],
        'ifm' =>
          [
            't' =>
              [
                0 => 'application/vnd.shana.informed.formdata',
              ],
          ],
        'iges' =>
          [
            't' =>
              [
                0 => 'model/iges',
              ],
          ],
        'igl' =>
          [
            't' =>
              [
                0 => 'application/vnd.igloader',
              ],
          ],
        'igm' =>
          [
            't' =>
              [
                0 => 'application/vnd.insors.igm',
              ],
          ],
        'igs' =>
          [
            't' =>
              [
                0 => 'model/iges',
              ],
          ],
        'igx' =>
          [
            't' =>
              [
                0 => 'application/vnd.micrografx.igx',
              ],
          ],
        'iif' =>
          [
            't' =>
              [
                0 => 'application/vnd.shana.informed.interchange',
              ],
          ],
        'ilbm' =>
          [
            't' =>
              [
                0 => 'image/x-ilbm',
              ],
          ],
        'ime' =>
          [
            't' =>
              [
                0 => 'text/x-imelody',
              ],
          ],
        'img' =>
          [
            't' =>
              [
                0 => 'application/vnd.efi.img',
              ],
          ],
        'img.xz' =>
          [
            't' =>
              [
                0 => 'application/x-raw-disk-image-xz-compressed',
              ],
          ],
        'imp' =>
          [
            't' =>
              [
                0 => 'application/vnd.accpac.simply.imp',
              ],
          ],
        'ims' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-ims',
              ],
          ],
        'imy' =>
          [
            't' =>
              [
                0 => 'text/x-imelody',
              ],
          ],
        'in' =>
          [
            't' =>
              [
                0 => 'text/plain',
              ],
          ],
        'ink' =>
          [
            't' =>
              [
                0 => 'application/inkml+xml',
              ],
          ],
        'inkml' =>
          [
            't' =>
              [
                0 => 'application/inkml+xml',
              ],
          ],
        'ins' =>
          [
            't' =>
              [
                0 => 'text/x-tex',
              ],
          ],
        'install' =>
          [
            't' =>
              [
                0 => 'application/x-install-instructions',
              ],
          ],
        'iota' =>
          [
            't' =>
              [
                0 => 'application/vnd.astraea-software.iota',
              ],
          ],
        'ipfix' =>
          [
            't' =>
              [
                0 => 'application/ipfix',
              ],
          ],
        'ipk' =>
          [
            't' =>
              [
                0 => 'application/vnd.shana.informed.package',
              ],
          ],
        'ips' =>
          [
            't' =>
              [
                0 => 'application/x-ips-patch',
              ],
          ],
        'iptables' =>
          [
            't' =>
              [
                0 => 'text/x-iptables',
              ],
          ],
        'ipynb' =>
          [
            't' =>
              [
                0 => 'application/x-ipynb+json',
              ],
          ],
        'irm' =>
          [
            't' =>
              [
                0 => 'application/vnd.ibm.rights-management',
              ],
          ],
        'irp' =>
          [
            't' =>
              [
                0 => 'application/vnd.irepository.package+xml',
              ],
          ],
        'iso' =>
          [
            't' =>
              [
                0 => 'application/vnd.efi.iso',
                1 => 'application/x-sega-cd-rom',
                2 => 'application/x-sega-pico-rom',
                3 => 'application/x-saturn-rom',
                4 => 'application/x-dreamcast-rom',
                5 => 'application/x-wii-rom',
                6 => 'application/x-gamecube-rom',
              ],
          ],
        'iso9660' =>
          [
            't' =>
              [
                0 => 'application/vnd.efi.iso',
              ],
          ],
        'it' =>
          [
            't' =>
              [
                0 => 'audio/x-it',
              ],
          ],
        'it87' =>
          [
            't' =>
              [
                0 => 'application/x-it87',
              ],
          ],
        'itp' =>
          [
            't' =>
              [
                0 => 'application/vnd.shana.informed.formtemplate',
              ],
          ],
        'its' =>
          [
            't' =>
              [
                0 => 'application/its+xml',
              ],
          ],
        'ivp' =>
          [
            't' =>
              [
                0 => 'application/vnd.immervision-ivp',
              ],
          ],
        'ivu' =>
          [
            't' =>
              [
                0 => 'application/vnd.immervision-ivu',
              ],
          ],
        'j2c' =>
          [
            't' =>
              [
                0 => 'image/x-jp2-codestream',
              ],
          ],
        'j2k' =>
          [
            't' =>
              [
                0 => 'image/x-jp2-codestream',
              ],
          ],
        'jad' =>
          [
            't' =>
              [
                0 => 'text/vnd.sun.j2me.app-descriptor',
              ],
          ],
        'jam' =>
          [
            't' =>
              [
                0 => 'application/vnd.jam',
              ],
          ],
        'jar' =>
          [
            't' =>
              [
                0 => 'application/java-archive',
              ],
          ],
        'java' =>
          [
            't' =>
              [
                0 => 'text/x-java-source',
                1 => 'text/x-java',
              ],
          ],
        'jceks' =>
          [
            't' =>
              [
                0 => 'application/x-java-jce-keystore',
              ],
          ],
        'jfif' =>
          [
            't' =>
              [
                0 => 'image/jpeg',
              ],
          ],
        'jisp' =>
          [
            't' =>
              [
                0 => 'application/vnd.jisp',
              ],
          ],
        'jks' =>
          [
            't' =>
              [
                0 => 'application/x-java-keystore',
              ],
          ],
        'jl' =>
          [
            't' =>
              [
                0 => 'text/julia',
              ],
          ],
        'jlt' =>
          [
            't' =>
              [
                0 => 'application/vnd.hp-jlyt',
              ],
          ],
        'jng' =>
          [
            't' =>
              [
                0 => 'image/x-jng',
              ],
          ],
        'jnlp' =>
          [
            't' =>
              [
                0 => 'application/x-java-jnlp-file',
              ],
          ],
        'joda' =>
          [
            't' =>
              [
                0 => 'application/vnd.joost.joda-archive',
              ],
          ],
        'jp2' =>
          [
            't' =>
              [
                0 => 'image/jp2',
              ],
          ],
        'jpc' =>
          [
            't' =>
              [
                0 => 'image/x-jp2-codestream',
              ],
          ],
        'jpe' =>
          [
            't' =>
              [
                0 => 'image/jpeg',
              ],
          ],
        'jpeg' =>
          [
            't' =>
              [
                0 => 'image/jpeg',
              ],
          ],
        'jpf' =>
          [
            't' =>
              [
                0 => 'image/jpx',
              ],
          ],
        'jpg' =>
          [
            't' =>
              [
                0 => 'image/jpeg',
              ],
          ],
        'jpg2' =>
          [
            't' =>
              [
                0 => 'image/jp2',
              ],
          ],
        'jpgm' =>
          [
            't' =>
              [
                0 => 'video/jpm',
                1 => 'image/jpm',
              ],
          ],
        'jpgv' =>
          [
            't' =>
              [
                0 => 'video/jpeg',
              ],
          ],
        'jpm' =>
          [
            't' =>
              [
                0 => 'video/jpm',
                1 => 'image/jpm',
              ],
          ],
        'jpr' =>
          [
            't' =>
              [
                0 => 'application/x-jbuilder-project',
              ],
          ],
        'jpx' =>
          [
            't' =>
              [
                0 => 'application/x-jbuilder-project',
                1 => 'image/jpx',
              ],
          ],
        'jrd' =>
          [
            't' =>
              [
                0 => 'application/jrd+json',
              ],
          ],
        'js' =>
          [
            't' =>
              [
                0 => 'text/javascript',
              ],
          ],
        'jse' =>
          [
            't' =>
              [
                0 => 'text/jscript.encode',
              ],
          ],
        'jsm' =>
          [
            't' =>
              [
                0 => 'text/javascript',
              ],
          ],
        'json' =>
          [
            't' =>
              [
                0 => 'application/json',
                1 => 'application/schema+json',
              ],
          ],
        'json-patch' =>
          [
            't' =>
              [
                0 => 'application/json-patch+json',
              ],
          ],
        'json5' =>
          [
            't' =>
              [
                0 => 'application/json5',
              ],
          ],
        'jsonld' =>
          [
            't' =>
              [
                0 => 'application/ld+json',
              ],
          ],
        'jsonml' =>
          [
            't' =>
              [
                0 => 'application/jsonml+json',
              ],
          ],
        'jxl' =>
          [
            't' =>
              [
                0 => 'image/jxl',
              ],
          ],
        'jxr' =>
          [
            't' =>
              [
                0 => 'image/jxr',
              ],
          ],
        'k25' =>
          [
            't' =>
              [
                0 => 'image/x-kodak-k25',
              ],
          ],
        'k7' =>
          [
            't' =>
              [
                0 => 'application/x-thomson-cassette',
              ],
          ],
        'kar' =>
          [
            't' =>
              [
                0 => 'audio/midi',
              ],
          ],
        'karbon' =>
          [
            't' =>
              [
                0 => 'application/vnd.kde.karbon',
                1 => 'application/x-karbon',
              ],
          ],
        'kcf' =>
          [
            't' =>
              [
                0 => 'image/x-kiss-cel',
              ],
          ],
        'kdc' =>
          [
            't' =>
              [
                0 => 'image/x-kodak-kdc',
              ],
          ],
        'kdelnk' =>
          [
            't' =>
              [
                0 => 'application/x-desktop',
              ],
          ],
        'kexi' =>
          [
            't' =>
              [
                0 => 'application/x-kexiproject-sqlite2',
                1 => 'application/x-kexiproject-sqlite3',
              ],
          ],
        'kexic' =>
          [
            't' =>
              [
                0 => 'application/x-kexi-connectiondata',
              ],
          ],
        'kexis' =>
          [
            't' =>
              [
                0 => 'application/x-kexiproject-shortcut',
              ],
          ],
        'key' =>
          [
            't' =>
              [
                0 => 'application/pgp-keys',
                1 => 'application/vnd.apple.keynote',
              ],
          ],
        'kfo' =>
          [
            't' =>
              [
                0 => 'application/vnd.kde.kformula',
                1 => 'application/x-kformula',
              ],
          ],
        'kfx' =>
          [
            't' =>
              [
                0 => 'application/vnd.amazon.mobi8-ebook',
              ],
          ],
        'kia' =>
          [
            't' =>
              [
                0 => 'application/vnd.kidspiration',
              ],
          ],
        'kil' =>
          [
            't' =>
              [
                0 => 'application/x-killustrator',
              ],
          ],
        'kino' =>
          [
            't' =>
              [
                0 => 'application/smil+xml',
              ],
          ],
        'kml' =>
          [
            't' =>
              [
                0 => 'application/vnd.google-earth.kml+xml',
              ],
          ],
        'kmz' =>
          [
            't' =>
              [
                0 => 'application/vnd.google-earth.kmz',
              ],
          ],
        'kne' =>
          [
            't' =>
              [
                0 => 'application/vnd.kinar',
              ],
          ],
        'knp' =>
          [
            't' =>
              [
                0 => 'application/vnd.kinar',
              ],
          ],
        'kon' =>
          [
            't' =>
              [
                0 => 'application/vnd.kde.kontour',
                1 => 'application/x-kontour',
              ],
          ],
        'kpm' =>
          [
            't' =>
              [
                0 => 'application/x-kpovmodeler',
              ],
          ],
        'kpr' =>
          [
            't' =>
              [
                0 => 'application/vnd.kde.kpresenter',
                1 => 'application/x-kpresenter',
              ],
          ],
        'kpt' =>
          [
            't' =>
              [
                0 => 'application/vnd.kde.kpresenter',
                1 => 'application/x-kpresenter',
              ],
          ],
        'kpxx' =>
          [
            't' =>
              [
                0 => 'application/vnd.ds-keypoint',
              ],
          ],
        'kra' =>
          [
            't' =>
              [
                0 => 'application/x-krita',
              ],
          ],
        'krz' =>
          [
            't' =>
              [
                0 => 'application/x-krita',
              ],
          ],
        'ks' =>
          [
            't' =>
              [
                0 => 'application/x-java-keystore',
              ],
          ],
        'ksp' =>
          [
            't' =>
              [
                0 => 'application/vnd.kde.kspread',
                1 => 'application/x-kspread',
              ],
          ],
        'ksy' =>
          [
            't' =>
              [
                0 => 'text/x-kaitai-struct',
              ],
          ],
        'kt' =>
          [
            't' =>
              [
                0 => 'text/x-kotlin',
              ],
          ],
        'ktr' =>
          [
            't' =>
              [
                0 => 'application/vnd.kahootz',
              ],
          ],
        'ktx' =>
          [
            't' =>
              [
                0 => 'image/ktx',
              ],
          ],
        'ktx2' =>
          [
            't' =>
              [
                0 => 'image/ktx2',
              ],
          ],
        'ktz' =>
          [
            't' =>
              [
                0 => 'application/vnd.kahootz',
              ],
          ],
        'kud' =>
          [
            't' =>
              [
                0 => 'application/x-kugar',
              ],
          ],
        'kwd' =>
          [
            't' =>
              [
                0 => 'application/vnd.kde.kword',
                1 => 'application/x-kword',
              ],
          ],
        'kwt' =>
          [
            't' =>
              [
                0 => 'application/vnd.kde.kword',
                1 => 'application/x-kword',
              ],
          ],
        'la' =>
          [
            't' =>
              [
                0 => 'application/x-shared-library-la',
              ],
          ],
        'lasxml' =>
          [
            't' =>
              [
                0 => 'application/vnd.las.las+xml',
              ],
          ],
        'latex' =>
          [
            't' =>
              [
                0 => 'application/x-latex',
                1 => 'text/x-tex',
              ],
          ],
        'lbd' =>
          [
            't' =>
              [
                0 => 'application/vnd.llamagraphics.life-balance.desktop',
              ],
          ],
        'lbe' =>
          [
            't' =>
              [
                0 => 'application/vnd.llamagraphics.life-balance.exchange+xml',
              ],
          ],
        'lbm' =>
          [
            't' =>
              [
                0 => 'image/x-ilbm',
              ],
          ],
        'ldif' =>
          [
            't' =>
              [
                0 => 'text/x-ldif',
              ],
          ],
        'les' =>
          [
            't' =>
              [
                0 => 'application/vnd.hhe.lesson-player',
              ],
          ],
        'lha' =>
          [
            't' =>
              [
                0 => 'application/x-lha',
              ],
          ],
        'lhs' =>
          [
            't' =>
              [
                0 => 'text/x-literate-haskell',
              ],
          ],
        'lhz' =>
          [
            't' =>
              [
                0 => 'application/x-lhz',
              ],
          ],
        'lib' =>
          [
            't' =>
              [
                0 => 'application/x-archive',
              ],
          ],
        'link66' =>
          [
            't' =>
              [
                0 => 'application/vnd.route66.link66+xml',
              ],
          ],
        'lisp' =>
          [
            't' =>
              [
                0 => 'text/x-common-lisp',
              ],
          ],
        'list' =>
          [
            't' =>
              [
                0 => 'text/plain',
              ],
          ],
        'list3820' =>
          [
            't' =>
              [
                0 => 'application/vnd.ibm.modcap',
              ],
          ],
        'listafp' =>
          [
            't' =>
              [
                0 => 'application/vnd.ibm.modcap',
              ],
          ],
        'lmdb' =>
          [
            't' =>
              [
                0 => 'application/x-lmdb',
              ],
          ],
        'lnk' =>
          [
            't' =>
              [
                0 => 'application/x-ms-shortcut',
              ],
          ],
        'lnx' =>
          [
            't' =>
              [
                0 => 'application/x-atari-lynx-rom',
              ],
          ],
        'loas' =>
          [
            't' =>
              [
                0 => 'audio/usac',
              ],
          ],
        'log' =>
          [
            't' =>
              [
                0 => 'text/plain',
                1 => 'text/x-log',
              ],
          ],
        'lostxml' =>
          [
            't' =>
              [
                0 => 'application/lost+xml',
              ],
          ],
        'lrf' =>
          [
            't' =>
              [
                0 => 'application/octet-stream',
                1 => 'application/x-sony-bbeb',
              ],
          ],
        'lrm' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-lrm',
              ],
          ],
        'lrv' =>
          [
            't' =>
              [
                0 => 'video/mp4',
              ],
          ],
        'lrz' =>
          [
            't' =>
              [
                0 => 'application/x-lrzip',
              ],
          ],
        'ltf' =>
          [
            't' =>
              [
                0 => 'application/vnd.frogans.ltf',
              ],
          ],
        'ltx' =>
          [
            't' =>
              [
                0 => 'text/x-tex',
              ],
          ],
        'lua' =>
          [
            't' =>
              [
                0 => 'text/x-lua',
              ],
          ],
        'lvp' =>
          [
            't' =>
              [
                0 => 'audio/vnd.lucent.voice',
              ],
          ],
        'lwo' =>
          [
            't' =>
              [
                0 => 'image/x-lwo',
              ],
          ],
        'lwob' =>
          [
            't' =>
              [
                0 => 'image/x-lwo',
              ],
          ],
        'lwp' =>
          [
            't' =>
              [
                0 => 'application/vnd.lotus-wordpro',
              ],
          ],
        'lws' =>
          [
            't' =>
              [
                0 => 'image/x-lws',
              ],
          ],
        'ly' =>
          [
            't' =>
              [
                0 => 'text/x-lilypond',
              ],
          ],
        'lyx' =>
          [
            't' =>
              [
                0 => 'application/x-lyx',
              ],
          ],
        'lz' =>
          [
            't' =>
              [
                0 => 'application/x-lzip',
              ],
          ],
        'lz4' =>
          [
            't' =>
              [
                0 => 'application/x-lz4',
              ],
          ],
        'lzh' =>
          [
            't' =>
              [
                0 => 'application/x-lha',
              ],
          ],
        'lzma' =>
          [
            't' =>
              [
                0 => 'application/x-lzma',
              ],
          ],
        'lzo' =>
          [
            't' =>
              [
                0 => 'application/x-lzop',
              ],
          ],
        'm' =>
          [
            't' =>
              [
                0 => 'text/x-objcsrc',
                1 => 'text/x-matlab',
              ],
          ],
        'm13' =>
          [
            't' =>
              [
                0 => 'application/x-msmediaview',
              ],
          ],
        'm14' =>
          [
            't' =>
              [
                0 => 'application/x-msmediaview',
              ],
          ],
        'm15' =>
          [
            't' =>
              [
                0 => 'audio/x-mod',
              ],
          ],
        'm1u' =>
          [
            't' =>
              [
                0 => 'video/vnd.mpegurl',
              ],
          ],
        'm1v' =>
          [
            't' =>
              [
                0 => 'video/mpeg',
              ],
          ],
        'm21' =>
          [
            't' =>
              [
                0 => 'application/mp21',
              ],
          ],
        'm2a' =>
          [
            't' =>
              [
                0 => 'audio/mpeg',
              ],
          ],
        'm2t' =>
          [
            't' =>
              [
                0 => 'video/mp2t',
              ],
          ],
        'm2ts' =>
          [
            't' =>
              [
                0 => 'video/mp2t',
              ],
          ],
        'm2v' =>
          [
            't' =>
              [
                0 => 'video/mpeg',
              ],
          ],
        'm3a' =>
          [
            't' =>
              [
                0 => 'audio/mpeg',
              ],
          ],
        'm3u' =>
          [
            't' =>
              [
                0 => 'audio/x-mpegurl',
                1 => 'application/vnd.apple.mpegurl',
              ],
          ],
        'm3u8' =>
          [
            't' =>
              [
                0 => 'application/vnd.apple.mpegurl',
                1 => 'audio/x-mpegurl',
              ],
          ],
        'm4' =>
          [
            't' =>
              [
                0 => 'application/x-m4',
              ],
          ],
        'm4a' =>
          [
            't' =>
              [
                0 => 'audio/mp4',
              ],
          ],
        'm4b' =>
          [
            't' =>
              [
                0 => 'audio/x-m4b',
              ],
          ],
        'm4r' =>
          [
            't' =>
              [
                0 => 'audio/x-m4r',
              ],
          ],
        'm4u' =>
          [
            't' =>
              [
                0 => 'video/vnd.mpegurl',
              ],
          ],
        'm4v' =>
          [
            't' =>
              [
                0 => 'video/mp4',
              ],
          ],
        'm7' =>
          [
            't' =>
              [
                0 => 'application/x-thomson-cartridge-memo7',
              ],
          ],
        'ma' =>
          [
            't' =>
              [
                0 => 'application/mathematica',
              ],
          ],
        'mab' =>
          [
            't' =>
              [
                0 => 'application/x-markaby',
              ],
          ],
        'mads' =>
          [
            't' =>
              [
                0 => 'application/mads+xml',
              ],
          ],
        'mag' =>
          [
            't' =>
              [
                0 => 'application/vnd.ecowin.chart',
              ],
          ],
        'mak' =>
          [
            't' =>
              [
                0 => 'text/x-makefile',
              ],
          ],
        'maker' =>
          [
            't' =>
              [
                0 => 'application/vnd.framemaker',
              ],
          ],
        'man' =>
          [
            't' =>
              [
                0 => 'text/troff',
                1 => 'application/x-troff-man',
              ],
          ],
        'manifest' =>
          [
            't' =>
              [
                0 => 'text/cache-manifest',
              ],
          ],
        'mar' =>
          [
            't' =>
              [
                0 => 'application/octet-stream',
              ],
          ],
        'markdown' =>
          [
            't' =>
              [
                0 => 'text/markdown',
              ],
          ],
        'mathml' =>
          [
            't' =>
              [
                0 => 'application/mathml+xml',
              ],
          ],
        'mb' =>
          [
            't' =>
              [
                0 => 'application/mathematica',
              ],
          ],
        'mbk' =>
          [
            't' =>
              [
                0 => 'application/vnd.mobius.mbk',
              ],
          ],
        'mbox' =>
          [
            't' =>
              [
                0 => 'application/mbox',
              ],
          ],
        'mc1' =>
          [
            't' =>
              [
                0 => 'application/vnd.medcalcdata',
              ],
          ],
        'mc2' =>
          [
            't' =>
              [
                0 => 'text/vnd.senx.warpscript',
              ],
          ],
        'mcd' =>
          [
            't' =>
              [
                0 => 'application/vnd.mcd',
              ],
          ],
        'mcurl' =>
          [
            't' =>
              [
                0 => 'text/vnd.curl.mcurl',
              ],
          ],
        'md' =>
          [
            't' =>
              [
                0 => 'text/markdown',
                1 => 'application/x-genesis-rom',
              ],
          ],
        'mdb' =>
          [
            't' =>
              [
                0 => 'application/x-lmdb',
                1 => 'application/vnd.ms-access',
              ],
          ],
        'mdi' =>
          [
            't' =>
              [
                0 => 'image/vnd.ms-modi',
              ],
          ],
        'mdx' =>
          [
            't' =>
              [
                0 => 'application/x-genesis-32x-rom',
              ],
          ],
        'me' =>
          [
            't' =>
              [
                0 => 'text/troff',
                1 => 'text/x-troff-me',
              ],
          ],
        'med' =>
          [
            't' =>
              [
                0 => 'audio/x-mod',
              ],
          ],
        'mesh' =>
          [
            't' =>
              [
                0 => 'model/mesh',
              ],
          ],
        'meta4' =>
          [
            't' =>
              [
                0 => 'application/metalink4+xml',
              ],
          ],
        'metalink' =>
          [
            't' =>
              [
                0 => 'application/metalink+xml',
              ],
          ],
        'mets' =>
          [
            't' =>
              [
                0 => 'application/mets+xml',
              ],
          ],
        'mfm' =>
          [
            't' =>
              [
                0 => 'application/vnd.mfmp',
              ],
          ],
        'mft' =>
          [
            't' =>
              [
                0 => 'application/rpki-manifest',
              ],
          ],
        'mgp' =>
          [
            't' =>
              [
                0 => 'application/vnd.osgeo.mapguide.package',
                1 => 'application/x-magicpoint',
              ],
          ],
        'mgz' =>
          [
            't' =>
              [
                0 => 'application/vnd.proteus.magazine',
              ],
          ],
        'mht' =>
          [
            't' =>
              [
                0 => 'application/x-mimearchive',
              ],
          ],
        'mhtml' =>
          [
            't' =>
              [
                0 => 'application/x-mimearchive',
              ],
          ],
        'mid' =>
          [
            't' =>
              [
                0 => 'audio/midi',
              ],
          ],
        'midi' =>
          [
            't' =>
              [
                0 => 'audio/midi',
              ],
          ],
        'mie' =>
          [
            't' =>
              [
                0 => 'application/x-mie',
              ],
          ],
        'mif' =>
          [
            't' =>
              [
                0 => 'application/vnd.mif',
                1 => 'application/x-mif',
              ],
          ],
        'mime' =>
          [
            't' =>
              [
                0 => 'message/rfc822',
              ],
          ],
        'minipsf' =>
          [
            't' =>
              [
                0 => 'audio/x-minipsf',
              ],
          ],
        'mj2' =>
          [
            't' =>
              [
                0 => 'video/mj2',
              ],
          ],
        'mjp2' =>
          [
            't' =>
              [
                0 => 'video/mj2',
              ],
          ],
        'mjpeg' =>
          [
            't' =>
              [
                0 => 'video/x-mjpeg',
              ],
          ],
        'mjpg' =>
          [
            't' =>
              [
                0 => 'video/x-mjpeg',
              ],
          ],
        'mjs' =>
          [
            't' =>
              [
                0 => 'text/javascript',
              ],
          ],
        'mk' =>
          [
            't' =>
              [
                0 => 'text/x-makefile',
              ],
          ],
        'mk3d' =>
          [
            't' =>
              [
                0 => 'video/x-matroska',
                1 => 'video/x-matroska-3d',
              ],
          ],
        'mka' =>
          [
            't' =>
              [
                0 => 'audio/x-matroska',
              ],
          ],
        'mkd' =>
          [
            't' =>
              [
                0 => 'text/markdown',
              ],
          ],
        'mks' =>
          [
            't' =>
              [
                0 => 'video/x-matroska',
              ],
          ],
        'mkv' =>
          [
            't' =>
              [
                0 => 'video/x-matroska',
              ],
          ],
        'ml' =>
          [
            't' =>
              [
                0 => 'text/x-ocaml',
              ],
          ],
        'mli' =>
          [
            't' =>
              [
                0 => 'text/x-ocaml',
              ],
          ],
        'mlp' =>
          [
            't' =>
              [
                0 => 'application/vnd.dolby.mlp',
              ],
          ],
        'mm' =>
          [
            't' =>
              [
                0 => 'text/x-objc++src',
                1 => 'text/x-troff-mm',
              ],
          ],
        'mmd' =>
          [
            't' =>
              [
                0 => 'application/vnd.chipnuts.karaoke-mmd',
              ],
          ],
        'mmf' =>
          [
            't' =>
              [
                0 => 'application/vnd.smaf',
              ],
          ],
        'mml' =>
          [
            't' =>
              [
                0 => 'application/mathml+xml',
              ],
          ],
        'mmr' =>
          [
            't' =>
              [
                0 => 'image/vnd.fujixerox.edmics-mmr',
              ],
          ],
        'mng' =>
          [
            't' =>
              [
                0 => 'video/x-mng',
              ],
          ],
        'mny' =>
          [
            't' =>
              [
                0 => 'application/x-msmoney',
              ],
          ],
        'mo' =>
          [
            't' =>
              [
                0 => 'application/x-gettext-translation',
                1 => 'text/x-modelica',
              ],
          ],
        'mo3' =>
          [
            't' =>
              [
                0 => 'audio/x-mo3',
              ],
          ],
        'mobi' =>
          [
            't' =>
              [
                0 => 'application/x-mobipocket-ebook',
              ],
          ],
        'moc' =>
          [
            't' =>
              [
                0 => 'text/x-moc',
              ],
          ],
        'mod' =>
          [
            't' =>
              [
                0 => 'application/x-object',
                1 => 'audio/x-mod',
              ],
          ],
        'mods' =>
          [
            't' =>
              [
                0 => 'application/mods+xml',
              ],
          ],
        'mof' =>
          [
            't' =>
              [
                0 => 'text/x-mof',
              ],
          ],
        'moov' =>
          [
            't' =>
              [
                0 => 'video/quicktime',
              ],
          ],
        'mount' =>
          [
            't' =>
              [
                0 => 'text/x-systemd-unit',
              ],
          ],
        'mov' =>
          [
            't' =>
              [
                0 => 'video/quicktime',
              ],
          ],
        'movie' =>
          [
            't' =>
              [
                0 => 'video/x-sgi-movie',
              ],
          ],
        'mp+' =>
          [
            't' =>
              [
                0 => 'audio/x-musepack',
              ],
          ],
        'mp2' =>
          [
            't' =>
              [
                0 => 'audio/mpeg',
                1 => 'audio/mp2',
                2 => 'video/mpeg',
              ],
          ],
        'mp21' =>
          [
            't' =>
              [
                0 => 'application/mp21',
              ],
          ],
        'mp2a' =>
          [
            't' =>
              [
                0 => 'audio/mpeg',
              ],
          ],
        'mp3' =>
          [
            't' =>
              [
                0 => 'audio/mpeg',
              ],
          ],
        'mp4' =>
          [
            't' =>
              [
                0 => 'video/mp4',
              ],
          ],
        'mp4a' =>
          [
            't' =>
              [
                0 => 'audio/mp4',
              ],
          ],
        'mp4s' =>
          [
            't' =>
              [
                0 => 'application/mp4',
              ],
          ],
        'mp4v' =>
          [
            't' =>
              [
                0 => 'video/mp4',
              ],
          ],
        'mpc' =>
          [
            't' =>
              [
                0 => 'application/vnd.mophun.certificate',
                1 => 'audio/x-musepack',
              ],
          ],
        'mpe' =>
          [
            't' =>
              [
                0 => 'video/mpeg',
              ],
          ],
        'mpeg' =>
          [
            't' =>
              [
                0 => 'video/mpeg',
              ],
          ],
        'mpg' =>
          [
            't' =>
              [
                0 => 'video/mpeg',
              ],
          ],
        'mpg4' =>
          [
            't' =>
              [
                0 => 'video/mp4',
              ],
          ],
        'mpga' =>
          [
            't' =>
              [
                0 => 'audio/mpeg',
              ],
          ],
        'mpkg' =>
          [
            't' =>
              [
                0 => 'application/vnd.apple.installer+xml',
              ],
          ],
        'mpl' =>
          [
            't' =>
              [
                0 => 'text/x-mpl2',
                1 => 'video/mp2t',
              ],
          ],
        'mpls' =>
          [
            't' =>
              [
                0 => 'video/mp2t',
              ],
          ],
        'mpm' =>
          [
            't' =>
              [
                0 => 'application/vnd.blueice.multipass',
              ],
          ],
        'mpn' =>
          [
            't' =>
              [
                0 => 'application/vnd.mophun.application',
              ],
          ],
        'mpp' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-project',
                1 => 'audio/x-musepack',
              ],
          ],
        'mpt' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-project',
              ],
          ],
        'mpy' =>
          [
            't' =>
              [
                0 => 'application/vnd.ibm.minipay',
              ],
          ],
        'mqy' =>
          [
            't' =>
              [
                0 => 'application/vnd.mobius.mqy',
              ],
          ],
        'mrc' =>
          [
            't' =>
              [
                0 => 'application/marc',
              ],
          ],
        'mrcx' =>
          [
            't' =>
              [
                0 => 'application/marcxml+xml',
              ],
          ],
        'mrl' =>
          [
            't' =>
              [
                0 => 'text/x-mrml',
              ],
          ],
        'mrml' =>
          [
            't' =>
              [
                0 => 'text/x-mrml',
              ],
          ],
        'mrpack' =>
          [
            't' =>
              [
                0 => 'application/x-modrinth-modpack+zip',
              ],
          ],
        'mrw' =>
          [
            't' =>
              [
                0 => 'image/x-minolta-mrw',
              ],
          ],
        'ms' =>
          [
            't' =>
              [
                0 => 'text/troff',
                1 => 'text/x-troff-ms',
              ],
          ],
        'mscml' =>
          [
            't' =>
              [
                0 => 'application/mediaservercontrol+xml',
              ],
          ],
        'mseed' =>
          [
            't' =>
              [
                0 => 'application/vnd.fdsn.mseed',
              ],
          ],
        'mseq' =>
          [
            't' =>
              [
                0 => 'application/vnd.mseq',
              ],
          ],
        'msf' =>
          [
            't' =>
              [
                0 => 'application/vnd.epson.msf',
              ],
          ],
        'msh' =>
          [
            't' =>
              [
                0 => 'model/mesh',
              ],
          ],
        'msi' =>
          [
            't' =>
              [
                0 => 'application/x-msdownload',
                1 => 'application/x-msi',
              ],
          ],
        'msix' =>
          [
            't' =>
              [
                0 => 'application/msix',
              ],
          ],
        'msixbundle' =>
          [
            't' =>
              [
                0 => 'application/msixbundle',
              ],
          ],
        'msl' =>
          [
            't' =>
              [
                0 => 'application/vnd.mobius.msl',
              ],
          ],
        'msod' =>
          [
            't' =>
              [
                0 => 'image/x-msod',
              ],
          ],
        'msp' =>
          [
            't' =>
              [
                0 => 'application/microsoftpatch',
              ],
          ],
        'msty' =>
          [
            't' =>
              [
                0 => 'application/vnd.muvee.style',
              ],
          ],
        'msu' =>
          [
            't' =>
              [
                0 => 'application/microsoftupdate',
              ],
          ],
        'msx' =>
          [
            't' =>
              [
                0 => 'application/x-msx-rom',
              ],
          ],
        'mtl' =>
          [
            't' =>
              [
                0 => 'model/mtl',
              ],
          ],
        'mtm' =>
          [
            't' =>
              [
                0 => 'audio/x-mod',
              ],
          ],
        'mts' =>
          [
            't' =>
              [
                0 => 'video/mp2t',
              ],
          ],
        'mup' =>
          [
            't' =>
              [
                0 => 'text/x-mup',
              ],
          ],
        'mus' =>
          [
            't' =>
              [
                0 => 'application/vnd.musician',
              ],
          ],
        'musicxml' =>
          [
            't' =>
              [
                0 => 'application/vnd.recordare.musicxml+xml',
              ],
          ],
        'mvb' =>
          [
            't' =>
              [
                0 => 'application/x-msmediaview',
              ],
          ],
        'mwf' =>
          [
            't' =>
              [
                0 => 'application/vnd.mfer',
              ],
          ],
        'mxf' =>
          [
            't' =>
              [
                0 => 'application/mxf',
              ],
          ],
        'mxl' =>
          [
            't' =>
              [
                0 => 'application/vnd.recordare.musicxml',
              ],
          ],
        'mxmf' =>
          [
            't' =>
              [
                0 => 'audio/mobile-xmf',
              ],
          ],
        'mxml' =>
          [
            't' =>
              [
                0 => 'application/xv+xml',
              ],
          ],
        'mxs' =>
          [
            't' =>
              [
                0 => 'application/vnd.triscape.mxs',
              ],
          ],
        'mxu' =>
          [
            't' =>
              [
                0 => 'video/vnd.mpegurl',
              ],
          ],
        'n-gage' =>
          [
            't' =>
              [
                0 => 'application/vnd.nokia.n-gage.symbian.install',
              ],
          ],
        'n3' =>
          [
            't' =>
              [
                0 => 'text/n3',
              ],
          ],
        'n64' =>
          [
            't' =>
              [
                0 => 'application/x-n64-rom',
              ],
          ],
        'nb' =>
          [
            't' =>
              [
                0 => 'application/mathematica',
              ],
          ],
        'nbp' =>
          [
            't' =>
              [
                0 => 'application/vnd.wolfram.player',
              ],
          ],
        'nc' =>
          [
            't' =>
              [
                0 => 'application/x-netcdf',
              ],
          ],
        'ncx' =>
          [
            't' =>
              [
                0 => 'application/x-dtbncx+xml',
              ],
          ],
        'nds' =>
          [
            't' =>
              [
                0 => 'application/x-nintendo-ds-rom',
              ],
          ],
        'nef' =>
          [
            't' =>
              [
                0 => 'image/x-nikon-nef',
              ],
          ],
        'nes' =>
          [
            't' =>
              [
                0 => 'application/x-nes-rom',
              ],
          ],
        'nez' =>
          [
            't' =>
              [
                0 => 'application/x-nes-rom',
              ],
          ],
        'nfo' =>
          [
            't' =>
              [
                0 => 'text/x-nfo',
              ],
          ],
        'ngc' =>
          [
            't' =>
              [
                0 => 'application/x-neo-geo-pocket-color-rom',
              ],
          ],
        'ngdat' =>
          [
            't' =>
              [
                0 => 'application/vnd.nokia.n-gage.data',
              ],
          ],
        'ngp' =>
          [
            't' =>
              [
                0 => 'application/x-neo-geo-pocket-rom',
              ],
          ],
        'nim' =>
          [
            't' =>
              [
                0 => 'text/x-nim',
              ],
          ],
        'nimble' =>
          [
            't' =>
              [
                0 => 'text/x-nimscript',
              ],
          ],
        'nims' =>
          [
            't' =>
              [
                0 => 'text/x-nimscript',
              ],
          ],
        'nitf' =>
          [
            't' =>
              [
                0 => 'application/vnd.nitf',
              ],
          ],
        'nix' =>
          [
            't' =>
              [
                0 => 'text/x-nix',
              ],
          ],
        'nlu' =>
          [
            't' =>
              [
                0 => 'application/vnd.neurolanguage.nlu',
              ],
          ],
        'nml' =>
          [
            't' =>
              [
                0 => 'application/vnd.enliven',
              ],
          ],
        'nnd' =>
          [
            't' =>
              [
                0 => 'application/vnd.noblenet-directory',
              ],
          ],
        'nns' =>
          [
            't' =>
              [
                0 => 'application/vnd.noblenet-sealer',
              ],
          ],
        'nnw' =>
          [
            't' =>
              [
                0 => 'application/vnd.noblenet-web',
              ],
          ],
        'not' =>
          [
            't' =>
              [
                0 => 'text/x-mup',
              ],
          ],
        'npx' =>
          [
            't' =>
              [
                0 => 'image/vnd.net-fpx',
              ],
          ],
        'nrw' =>
          [
            't' =>
              [
                0 => 'image/x-nikon-nrw',
              ],
          ],
        'nsc' =>
          [
            't' =>
              [
                0 => 'application/x-conference',
                1 => 'application/x-netshow-channel',
              ],
          ],
        'nsf' =>
          [
            't' =>
              [
                0 => 'application/vnd.lotus-notes',
              ],
          ],
        'nsv' =>
          [
            't' =>
              [
                0 => 'video/x-nsv',
              ],
          ],
        'ntar' =>
          [
            't' =>
              [
                0 => 'application/x-pcapng',
              ],
          ],
        'ntf' =>
          [
            't' =>
              [
                0 => 'application/vnd.nitf',
              ],
          ],
        'nu' =>
          [
            't' =>
              [
                0 => 'application/x-nuscript',
              ],
          ],
        'numbers' =>
          [
            't' =>
              [
                0 => 'application/vnd.apple.numbers',
              ],
          ],
        'nzb' =>
          [
            't' =>
              [
                0 => 'application/x-nzb',
              ],
          ],
        'o' =>
          [
            't' =>
              [
                0 => 'application/x-object',
              ],
          ],
        'oa2' =>
          [
            't' =>
              [
                0 => 'application/vnd.fujitsu.oasys2',
              ],
          ],
        'oa3' =>
          [
            't' =>
              [
                0 => 'application/vnd.fujitsu.oasys3',
              ],
          ],
        'oas' =>
          [
            't' =>
              [
                0 => 'application/vnd.fujitsu.oasys',
              ],
          ],
        'obd' =>
          [
            't' =>
              [
                0 => 'application/x-msbinder',
              ],
          ],
        'obj' =>
          [
            't' =>
              [
                0 => 'application/x-tgif',
                1 => 'model/obj',
              ],
          ],
        'ocl' =>
          [
            't' =>
              [
                0 => 'text/x-ocl',
              ],
          ],
        'ocx' =>
          [
            't' =>
              [
                0 => 'application/vnd.microsoft.portable-executable',
              ],
          ],
        'oda' =>
          [
            't' =>
              [
                0 => 'application/oda',
              ],
          ],
        'odb' =>
          [
            't' =>
              [
                0 => 'application/vnd.oasis.opendocument.base',
              ],
          ],
        'odc' =>
          [
            't' =>
              [
                0 => 'application/vnd.oasis.opendocument.chart',
              ],
          ],
        'odf' =>
          [
            't' =>
              [
                0 => 'application/vnd.oasis.opendocument.formula',
              ],
          ],
        'odft' =>
          [
            't' =>
              [
                0 => 'application/vnd.oasis.opendocument.formula-template',
              ],
          ],
        'odg' =>
          [
            't' =>
              [
                0 => 'application/vnd.oasis.opendocument.graphics',
              ],
          ],
        'odi' =>
          [
            't' =>
              [
                0 => 'application/vnd.oasis.opendocument.image',
              ],
          ],
        'odm' =>
          [
            't' =>
              [
                0 => 'application/vnd.oasis.opendocument.text-master',
              ],
          ],
        'odp' =>
          [
            't' =>
              [
                0 => 'application/vnd.oasis.opendocument.presentation',
              ],
          ],
        'ods' =>
          [
            't' =>
              [
                0 => 'application/vnd.oasis.opendocument.spreadsheet',
              ],
          ],
        'odt' =>
          [
            't' =>
              [
                0 => 'application/vnd.oasis.opendocument.text',
              ],
          ],
        'oga' =>
          [
            't' =>
              [
                0 => 'audio/ogg',
                1 => 'audio/x-vorbis+ogg',
                2 => 'audio/x-flac+ogg',
                3 => 'audio/x-speex+ogg',
              ],
          ],
        'ogg' =>
          [
            't' =>
              [
                0 => 'audio/ogg',
                1 => 'video/ogg',
                2 => 'audio/x-vorbis+ogg',
                3 => 'audio/x-flac+ogg',
                4 => 'audio/x-speex+ogg',
                5 => 'video/x-theora+ogg',
              ],
          ],
        'ogm' =>
          [
            't' =>
              [
                0 => 'video/x-ogm+ogg',
              ],
          ],
        'ogv' =>
          [
            't' =>
              [
                0 => 'video/ogg',
              ],
          ],
        'ogx' =>
          [
            't' =>
              [
                0 => 'application/ogg',
              ],
          ],
        'old' =>
          [
            't' =>
              [
                0 => 'application/x-trash',
              ],
          ],
        'oleo' =>
          [
            't' =>
              [
                0 => 'application/x-oleo',
              ],
          ],
        'omdoc' =>
          [
            't' =>
              [
                0 => 'application/omdoc+xml',
              ],
          ],
        'onepkg' =>
          [
            't' =>
              [
                0 => 'application/onenote',
              ],
          ],
        'onetmp' =>
          [
            't' =>
              [
                0 => 'application/onenote',
              ],
          ],
        'onetoc' =>
          [
            't' =>
              [
                0 => 'application/onenote',
              ],
          ],
        'onetoc2' =>
          [
            't' =>
              [
                0 => 'application/onenote',
              ],
          ],
        'ooc' =>
          [
            't' =>
              [
                0 => 'text/x-ooc',
              ],
          ],
        'openvpn' =>
          [
            't' =>
              [
                0 => 'application/x-openvpn-profile',
              ],
          ],
        'opf' =>
          [
            't' =>
              [
                0 => 'application/oebps-package+xml',
              ],
          ],
        'opml' =>
          [
            't' =>
              [
                0 => 'text/x-opml+xml',
              ],
          ],
        'oprc' =>
          [
            't' =>
              [
                0 => 'application/vnd.palm',
              ],
          ],
        'opus' =>
          [
            't' =>
              [
                0 => 'audio/ogg',
                1 => 'audio/x-opus+ogg',
              ],
          ],
        'ora' =>
          [
            't' =>
              [
                0 => 'image/openraster',
              ],
          ],
        'orf' =>
          [
            't' =>
              [
                0 => 'image/x-olympus-orf',
              ],
          ],
        'org' =>
          [
            't' =>
              [
                0 => 'application/vnd.lotus-organizer',
                1 => 'text/org',
              ],
          ],
        'osf' =>
          [
            't' =>
              [
                0 => 'application/vnd.yamaha.openscoreformat',
              ],
          ],
        'osfpvg' =>
          [
            't' =>
              [
                0 => 'application/vnd.yamaha.openscoreformat.osfpvg+xml',
              ],
          ],
        'otc' =>
          [
            't' =>
              [
                0 => 'application/vnd.oasis.opendocument.chart-template',
              ],
          ],
        'otf' =>
          [
            't' =>
              [
                0 => 'font/otf',
                1 => 'application/vnd.oasis.opendocument.formula-template',
              ],
          ],
        'otg' =>
          [
            't' =>
              [
                0 => 'application/vnd.oasis.opendocument.graphics-template',
              ],
          ],
        'oth' =>
          [
            't' =>
              [
                0 => 'application/vnd.oasis.opendocument.text-web',
              ],
          ],
        'oti' =>
          [
            't' =>
              [
                0 => 'application/vnd.oasis.opendocument.image-template',
              ],
          ],
        'otm' =>
          [
            't' =>
              [
                0 => 'application/vnd.oasis.opendocument.text-master-template',
              ],
          ],
        'otp' =>
          [
            't' =>
              [
                0 => 'application/vnd.oasis.opendocument.presentation-template',
              ],
          ],
        'ots' =>
          [
            't' =>
              [
                0 => 'application/vnd.oasis.opendocument.spreadsheet-template',
              ],
          ],
        'ott' =>
          [
            't' =>
              [
                0 => 'application/vnd.oasis.opendocument.text-template',
              ],
          ],
        'ova' =>
          [
            't' =>
              [
                0 => 'application/ovf',
              ],
          ],
        'ovpn' =>
          [
            't' =>
              [
                0 => 'application/x-openvpn-profile',
              ],
          ],
        'owl' =>
          [
            't' =>
              [
                0 => 'application/rdf+xml',
              ],
          ],
        'owx' =>
          [
            't' =>
              [
                0 => 'application/owl+xml',
              ],
          ],
        'oxps' =>
          [
            't' =>
              [
                0 => 'application/oxps',
              ],
          ],
        'oxt' =>
          [
            't' =>
              [
                0 => 'application/vnd.openofficeorg.extension',
              ],
          ],
        'p' =>
          [
            't' =>
              [
                0 => 'text/x-pascal',
              ],
          ],
        'p10' =>
          [
            't' =>
              [
                0 => 'application/pkcs10',
              ],
          ],
        'p12' =>
          [
            't' =>
              [
                0 => 'application/pkcs12',
              ],
          ],
        'p65' =>
          [
            't' =>
              [
                0 => 'application/x-pagemaker',
              ],
          ],
        'p7b' =>
          [
            't' =>
              [
                0 => 'application/x-pkcs7-certificates',
              ],
          ],
        'p7c' =>
          [
            't' =>
              [
                0 => 'application/pkcs7-mime',
              ],
          ],
        'p7m' =>
          [
            't' =>
              [
                0 => 'application/pkcs7-mime',
              ],
          ],
        'p7r' =>
          [
            't' =>
              [
                0 => 'application/x-pkcs7-certreqresp',
              ],
          ],
        'p7s' =>
          [
            't' =>
              [
                0 => 'application/pkcs7-signature',
              ],
          ],
        'p8' =>
          [
            't' =>
              [
                0 => 'application/pkcs8',
              ],
          ],
        'p8e' =>
          [
            't' =>
              [
                0 => 'application/pkcs8-encrypted',
              ],
          ],
        'pack' =>
          [
            't' =>
              [
                0 => 'application/x-java-pack200',
              ],
          ],
        'pages' =>
          [
            't' =>
              [
                0 => 'application/vnd.apple.pages',
              ],
          ],
        'pak' =>
          [
            't' =>
              [
                0 => 'application/x-pak',
              ],
          ],
        'par2' =>
          [
            't' =>
              [
                0 => 'application/x-par2',
              ],
          ],
        'parquet' =>
          [
            't' =>
              [
                0 => 'application/vnd.apache.parquet',
              ],
          ],
        'part' =>
          [
            't' =>
              [
                0 => 'application/x-partial-download',
              ],
          ],
        'pas' =>
          [
            't' =>
              [
                0 => 'text/x-pascal',
              ],
          ],
        'pat' =>
          [
            't' =>
              [
                0 => 'image/x-gimp-pat',
              ],
          ],
        'patch' =>
          [
            't' =>
              [
                0 => 'text/x-patch',
              ],
          ],
        'path' =>
          [
            't' =>
              [
                0 => 'text/x-systemd-unit',
              ],
          ],
        'paw' =>
          [
            't' =>
              [
                0 => 'application/vnd.pawaafile',
              ],
          ],
        'pbd' =>
          [
            't' =>
              [
                0 => 'application/vnd.powerbuilder6',
              ],
          ],
        'pbm' =>
          [
            't' =>
              [
                0 => 'image/x-portable-bitmap',
              ],
          ],
        'pcap' =>
          [
            't' =>
              [
                0 => 'application/vnd.tcpdump.pcap',
              ],
          ],
        'pcapng' =>
          [
            't' =>
              [
                0 => 'application/x-pcapng',
              ],
          ],
        'pcd' =>
          [
            't' =>
              [
                0 => 'image/x-photo-cd',
              ],
          ],
        'pce' =>
          [
            't' =>
              [
                0 => 'application/x-pc-engine-rom',
              ],
          ],
        'pcf' =>
          [
            't' =>
              [
                0 => 'application/x-font-pcf',
                1 => 'application/x-cisco-vpn-settings',
              ],
          ],
        'pcf.gz' =>
          [
            't' =>
              [
                0 => 'application/x-font-pcf',
              ],
          ],
        'pcf.z' =>
          [
            't' =>
              [
                0 => 'application/x-font-pcf',
              ],
          ],
        'pcl' =>
          [
            't' =>
              [
                0 => 'application/vnd.hp-pcl',
              ],
          ],
        'pclxl' =>
          [
            't' =>
              [
                0 => 'application/vnd.hp-pclxl',
              ],
          ],
        'pct' =>
          [
            't' =>
              [
                0 => 'image/x-pict',
              ],
          ],
        'pcurl' =>
          [
            't' =>
              [
                0 => 'application/vnd.curl.pcurl',
              ],
          ],
        'pcx' =>
          [
            't' =>
              [
                0 => 'image/vnd.zbrush.pcx',
              ],
          ],
        'pdb' =>
          [
            't' =>
              [
                0 => 'application/vnd.palm',
                1 => 'application/x-aportisdoc',
                2 => 'chemical/x-pdb',
                3 => 'application/x-ms-pdb',
              ],
          ],
        'pdc' =>
          [
            't' =>
              [
                0 => 'application/x-aportisdoc',
              ],
          ],
        'pdf' =>
          [
            't' =>
              [
                0 => 'application/pdf',
              ],
          ],
        'pdf.bz2' =>
          [
            't' =>
              [
                0 => 'application/x-bzpdf',
              ],
          ],
        'pdf.gz' =>
          [
            't' =>
              [
                0 => 'application/x-gzpdf',
              ],
          ],
        'pdf.lz' =>
          [
            't' =>
              [
                0 => 'application/x-lzpdf',
              ],
          ],
        'pdf.xz' =>
          [
            't' =>
              [
                0 => 'application/x-xzpdf',
              ],
          ],
        'pef' =>
          [
            't' =>
              [
                0 => 'image/x-pentax-pef',
              ],
          ],
        'pem' =>
          [
            't' =>
              [
                0 => 'application/x-x509-ca-cert',
              ],
          ],
        'perl' =>
          [
            't' =>
              [
                0 => 'application/x-perl',
              ],
          ],
        'pfa' =>
          [
            't' =>
              [
                0 => 'application/x-font-type1',
              ],
          ],
        'pfb' =>
          [
            't' =>
              [
                0 => 'application/x-font-type1',
              ],
          ],
        'pfm' =>
          [
            't' =>
              [
                0 => 'application/x-font-type1',
              ],
          ],
        'pfr' =>
          [
            't' =>
              [
                0 => 'application/font-tdpfr',
              ],
          ],
        'pfx' =>
          [
            't' =>
              [
                0 => 'application/pkcs12',
              ],
          ],
        'pgm' =>
          [
            't' =>
              [
                0 => 'image/x-portable-graymap',
              ],
          ],
        'pgn' =>
          [
            't' =>
              [
                0 => 'application/vnd.chess-pgn',
              ],
          ],
        'pgp' =>
          [
            't' =>
              [
                0 => 'application/pgp-encrypted',
                1 => 'application/pgp-keys',
                2 => 'application/pgp-signature',
              ],
          ],
        'php' =>
          [
            't' =>
              [
                0 => 'application/x-php',
              ],
          ],
        'php3' =>
          [
            't' =>
              [
                0 => 'application/x-php',
              ],
          ],
        'php4' =>
          [
            't' =>
              [
                0 => 'application/x-php',
              ],
          ],
        'php5' =>
          [
            't' =>
              [
                0 => 'application/x-php',
              ],
          ],
        'phps' =>
          [
            't' =>
              [
                0 => 'application/x-php',
              ],
          ],
        'pic' =>
          [
            't' =>
              [
                0 => 'image/x-pict',
              ],
          ],
        'pict' =>
          [
            't' =>
              [
                0 => 'image/x-pict',
              ],
          ],
        'pict1' =>
          [
            't' =>
              [
                0 => 'image/x-pict',
              ],
          ],
        'pict2' =>
          [
            't' =>
              [
                0 => 'image/x-pict',
              ],
          ],
        'pk' =>
          [
            't' =>
              [
                0 => 'application/x-tex-pk',
              ],
          ],
        'pkg' =>
          [
            't' =>
              [
                0 => 'application/octet-stream',
                1 => 'application/x-xar',
              ],
          ],
        'pki' =>
          [
            't' =>
              [
                0 => 'application/pkixcmp',
              ],
          ],
        'pkipath' =>
          [
            't' =>
              [
                0 => 'application/pkix-pkipath',
              ],
          ],
        'pkpass' =>
          [
            't' =>
              [
                0 => 'application/vnd.apple.pkpass',
              ],
          ],
        'pkr' =>
          [
            't' =>
              [
                0 => 'application/pgp-keys',
              ],
          ],
        'pl' =>
          [
            't' =>
              [
                0 => 'application/x-perl',
              ],
          ],
        'pla' =>
          [
            't' =>
              [
                0 => 'audio/x-iriver-pla',
              ],
          ],
        'plb' =>
          [
            't' =>
              [
                0 => 'application/vnd.3gpp.pic-bw-large',
              ],
          ],
        'plc' =>
          [
            't' =>
              [
                0 => 'application/vnd.mobius.plc',
              ],
          ],
        'plf' =>
          [
            't' =>
              [
                0 => 'application/vnd.pocketlearn',
              ],
          ],
        'pln' =>
          [
            't' =>
              [
                0 => 'application/x-planperfect',
              ],
          ],
        'pls' =>
          [
            't' =>
              [
                0 => 'application/pls+xml',
                1 => 'audio/x-scpls',
              ],
          ],
        'pm' =>
          [
            't' =>
              [
                0 => 'application/x-perl',
                1 => 'application/x-pagemaker',
              ],
          ],
        'pm6' =>
          [
            't' =>
              [
                0 => 'application/x-pagemaker',
              ],
          ],
        'pmd' =>
          [
            't' =>
              [
                0 => 'application/x-pagemaker',
              ],
          ],
        'pml' =>
          [
            't' =>
              [
                0 => 'application/vnd.ctc-posml',
              ],
          ],
        'png' =>
          [
            't' =>
              [
                0 => 'image/png',
                1 => 'image/apng',
              ],
          ],
        'pnm' =>
          [
            't' =>
              [
                0 => 'image/x-portable-anymap',
              ],
          ],
        'pntg' =>
          [
            't' =>
              [
                0 => 'image/x-macpaint',
              ],
          ],
        'po' =>
          [
            't' =>
              [
                0 => 'text/x-gettext-translation',
              ],
          ],
        'pod' =>
          [
            't' =>
              [
                0 => 'application/x-perl',
              ],
          ],
        'por' =>
          [
            't' =>
              [
                0 => 'application/x-spss-por',
              ],
          ],
        'portpkg' =>
          [
            't' =>
              [
                0 => 'application/vnd.macports.portpkg',
              ],
          ],
        'pot' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-powerpoint',
                1 => 'text/x-gettext-translation-template',
              ],
          ],
        'potm' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-powerpoint.template.macroenabled.12',
              ],
          ],
        'potx' =>
          [
            't' =>
              [
                0 => 'application/vnd.openxmlformats-officedocument.presentationml.template',
              ],
          ],
        'ppam' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-powerpoint.addin.macroenabled.12',
              ],
          ],
        'ppd' =>
          [
            't' =>
              [
                0 => 'application/vnd.cups-ppd',
              ],
          ],
        'ppm' =>
          [
            't' =>
              [
                0 => 'image/x-portable-pixmap',
              ],
          ],
        'pps' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-powerpoint',
              ],
          ],
        'ppsm' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-powerpoint.slideshow.macroenabled.12',
              ],
          ],
        'ppsx' =>
          [
            't' =>
              [
                0 => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
              ],
          ],
        'ppt' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-powerpoint',
              ],
          ],
        'pptm' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-powerpoint.presentation.macroenabled.12',
              ],
          ],
        'pptx' =>
          [
            't' =>
              [
                0 => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
              ],
          ],
        'ppz' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-powerpoint',
              ],
          ],
        'pqa' =>
          [
            't' =>
              [
                0 => 'application/vnd.palm',
              ],
          ],
        'prc' =>
          [
            't' =>
              [
                0 => 'application/x-mobipocket-ebook',
                1 => 'application/vnd.palm',
              ],
          ],
        'pre' =>
          [
            't' =>
              [
                0 => 'application/vnd.lotus-freelance',
              ],
          ],
        'prf' =>
          [
            't' =>
              [
                0 => 'application/pics-rules',
              ],
          ],
        'ps' =>
          [
            't' =>
              [
                0 => 'application/postscript',
              ],
          ],
        'ps.bz2' =>
          [
            't' =>
              [
                0 => 'application/x-bzpostscript',
              ],
          ],
        'ps.gz' =>
          [
            't' =>
              [
                0 => 'application/x-gzpostscript',
              ],
          ],
        'ps1' =>
          [
            't' =>
              [
                0 => 'application/x-powershell',
              ],
          ],
        'psb' =>
          [
            't' =>
              [
                0 => 'application/vnd.3gpp.pic-bw-small',
              ],
          ],
        'psd' =>
          [
            't' =>
              [
                0 => 'image/vnd.adobe.photoshop',
              ],
          ],
        'psf' =>
          [
            't' =>
              [
                0 => 'application/x-font-linux-psf',
                1 => 'audio/x-psf',
              ],
          ],
        'psf.gz' =>
          [
            't' =>
              [
                0 => 'application/x-gz-font-linux-psf',
              ],
          ],
        'psflib' =>
          [
            't' =>
              [
                0 => 'audio/x-psflib',
              ],
          ],
        'psid' =>
          [
            't' =>
              [
                0 => 'audio/prs.sid',
              ],
          ],
        'pskcxml' =>
          [
            't' =>
              [
                0 => 'application/pskc+xml',
              ],
          ],
        'psw' =>
          [
            't' =>
              [
                0 => 'application/x-pocket-word',
              ],
          ],
        'ptid' =>
          [
            't' =>
              [
                0 => 'application/vnd.pvi.ptid1',
              ],
          ],
        'pub' =>
          [
            't' =>
              [
                0 => 'application/x-mspublisher',
                1 => 'application/vnd.ms-publisher',
              ],
          ],
        'pvb' =>
          [
            't' =>
              [
                0 => 'application/vnd.3gpp.pic-bw-var',
              ],
          ],
        'pw' =>
          [
            't' =>
              [
                0 => 'application/x-pw',
              ],
          ],
        'pwn' =>
          [
            't' =>
              [
                0 => 'application/vnd.3m.post-it-notes',
              ],
          ],
        'pxd' =>
          [
            't' =>
              [
                0 => 'text/x-cython',
              ],
          ],
        'pxi' =>
          [
            't' =>
              [
                0 => 'text/x-cython',
              ],
          ],
        'py' =>
          [
            't' =>
              [
                0 => 'text/x-python3',
                1 => 'text/x-python2',
                2 => 'text/x-python',
              ],
          ],
        'py2' =>
          [
            't' =>
              [
                0 => 'text/x-python2',
              ],
          ],
        'py3' =>
          [
            't' =>
              [
                0 => 'text/x-python3',
              ],
          ],
        'pya' =>
          [
            't' =>
              [
                0 => 'audio/vnd.ms-playready.media.pya',
              ],
          ],
        'pyc' =>
          [
            't' =>
              [
                0 => 'application/x-python-bytecode',
              ],
          ],
        'pyi' =>
          [
            't' =>
              [
                0 => 'text/x-python3',
              ],
          ],
        'pyo' =>
          [
            't' =>
              [
                0 => 'application/x-python-bytecode',
              ],
          ],
        'pys' =>
          [
            't' =>
              [
                0 => 'application/x-pyspread-bz-spreadsheet',
              ],
          ],
        'pysu' =>
          [
            't' =>
              [
                0 => 'application/x-pyspread-spreadsheet',
              ],
          ],
        'pyv' =>
          [
            't' =>
              [
                0 => 'video/vnd.ms-playready.media.pyv',
              ],
          ],
        'pyx' =>
          [
            't' =>
              [
                0 => 'text/x-cython',
              ],
          ],
        'qam' =>
          [
            't' =>
              [
                0 => 'application/vnd.epson.quickanime',
              ],
          ],
        'qbo' =>
          [
            't' =>
              [
                0 => 'application/vnd.intu.qbo',
              ],
          ],
        'qbrew' =>
          [
            't' =>
              [
                0 => 'application/x-qbrew',
              ],
          ],
        'qcow' =>
          [
            't' =>
              [
                0 => 'application/x-qemu-disk',
              ],
          ],
        'qcow2' =>
          [
            't' =>
              [
                0 => 'application/x-qemu-disk',
              ],
          ],
        'qd' =>
          [
            't' =>
              [
                0 => 'application/x-raw-floppy-disk-image',
              ],
          ],
        'qed' =>
          [
            't' =>
              [
                0 => 'application/x-qed-disk',
              ],
          ],
        'qfx' =>
          [
            't' =>
              [
                0 => 'application/vnd.intu.qfx',
              ],
          ],
        'qif' =>
          [
            't' =>
              [
                0 => 'application/x-qw',
                1 => 'image/x-quicktime',
              ],
          ],
        'qml' =>
          [
            't' =>
              [
                0 => 'text/x-qml',
              ],
          ],
        'qmlproject' =>
          [
            't' =>
              [
                0 => 'text/x-qml',
              ],
          ],
        'qmltypes' =>
          [
            't' =>
              [
                0 => 'text/x-qml',
              ],
          ],
        'qoi' =>
          [
            't' =>
              [
                0 => 'image/qoi',
              ],
          ],
        'qp' =>
          [
            't' =>
              [
                0 => 'application/x-qpress',
              ],
          ],
        'qps' =>
          [
            't' =>
              [
                0 => 'application/vnd.publishare-delta-tree',
              ],
          ],
        'qpw' =>
          [
            't' =>
              [
                0 => 'application/x-quattropro',
              ],
          ],
        'qs' =>
          [
            't' =>
              [
                0 => 'application/sparql-query',
              ],
          ],
        'qt' =>
          [
            't' =>
              [
                0 => 'video/quicktime',
              ],
          ],
        'qti' =>
          [
            't' =>
              [
                0 => 'application/x-qtiplot',
              ],
          ],
        'qti.gz' =>
          [
            't' =>
              [
                0 => 'application/x-qtiplot',
              ],
          ],
        'qtif' =>
          [
            't' =>
              [
                0 => 'image/x-quicktime',
              ],
          ],
        'qtl' =>
          [
            't' =>
              [
                0 => 'application/x-quicktime-media-link',
              ],
          ],
        'qtvr' =>
          [
            't' =>
              [
                0 => 'video/quicktime',
              ],
          ],
        'qwd' =>
          [
            't' =>
              [
                0 => 'application/vnd.quark.quarkxpress',
              ],
          ],
        'qwt' =>
          [
            't' =>
              [
                0 => 'application/vnd.quark.quarkxpress',
              ],
          ],
        'qxb' =>
          [
            't' =>
              [
                0 => 'application/vnd.quark.quarkxpress',
              ],
          ],
        'qxd' =>
          [
            't' =>
              [
                0 => 'application/vnd.quark.quarkxpress',
              ],
          ],
        'qxl' =>
          [
            't' =>
              [
                0 => 'application/vnd.quark.quarkxpress',
              ],
          ],
        'qxp' =>
          [
            't' =>
              [
                0 => 'application/vnd.quark.quarkxpress',
              ],
          ],
        'qxt' =>
          [
            't' =>
              [
                0 => 'application/vnd.quark.quarkxpress',
              ],
          ],
        'ra' =>
          [
            't' =>
              [
                0 => 'audio/vnd.rn-realaudio',
              ],
          ],
        'raf' =>
          [
            't' =>
              [
                0 => 'image/x-fuji-raf',
              ],
          ],
        'ram' =>
          [
            't' =>
              [
                0 => 'application/ram',
              ],
          ],
        'raml' =>
          [
            't' =>
              [
                0 => 'application/raml+yaml',
              ],
          ],
        'rar' =>
          [
            't' =>
              [
                0 => 'application/vnd.rar',
              ],
          ],
        'ras' =>
          [
            't' =>
              [
                0 => 'image/x-cmu-raster',
              ],
          ],
        'raw' =>
          [
            't' =>
              [
                0 => 'image/x-panasonic-rw',
              ],
          ],
        'raw-disk-image' =>
          [
            't' =>
              [
                0 => 'application/vnd.efi.img',
              ],
          ],
        'raw-disk-image.xz' =>
          [
            't' =>
              [
                0 => 'application/x-raw-disk-image-xz-compressed',
              ],
          ],
        'rax' =>
          [
            't' =>
              [
                0 => 'audio/vnd.rn-realaudio',
              ],
          ],
        'rb' =>
          [
            't' =>
              [
                0 => 'application/x-ruby',
              ],
          ],
        'rcprofile' =>
          [
            't' =>
              [
                0 => 'application/vnd.ipunplugged.rcprofile',
              ],
          ],
        'rdf' =>
          [
            't' =>
              [
                0 => 'application/rdf+xml',
              ],
          ],
        'rdfs' =>
          [
            't' =>
              [
                0 => 'application/rdf+xml',
              ],
          ],
        'rdz' =>
          [
            't' =>
              [
                0 => 'application/vnd.data-vision.rdz',
              ],
          ],
        'reg' =>
          [
            't' =>
              [
                0 => 'text/x-ms-regedit',
              ],
          ],
        'rej' =>
          [
            't' =>
              [
                0 => 'text/x-reject',
              ],
          ],
        'rep' =>
          [
            't' =>
              [
                0 => 'application/vnd.businessobjects',
              ],
          ],
        'res' =>
          [
            't' =>
              [
                0 => 'application/x-dtbresource+xml',
                1 => 'application/x-godot-resource',
              ],
          ],
        'rgb' =>
          [
            't' =>
              [
                0 => 'image/x-rgb',
              ],
          ],
        'rif' =>
          [
            't' =>
              [
                0 => 'application/reginfo+xml',
              ],
          ],
        'rip' =>
          [
            't' =>
              [
                0 => 'audio/vnd.rip',
              ],
          ],
        'ris' =>
          [
            't' =>
              [
                0 => 'application/x-research-info-systems',
              ],
          ],
        'rl' =>
          [
            't' =>
              [
                0 => 'application/resource-lists+xml',
              ],
          ],
        'rlc' =>
          [
            't' =>
              [
                0 => 'image/vnd.fujixerox.edmics-rlc',
              ],
          ],
        'rld' =>
          [
            't' =>
              [
                0 => 'application/resource-lists-diff+xml',
              ],
          ],
        'rle' =>
          [
            't' =>
              [
                0 => 'image/rle',
              ],
          ],
        'rm' =>
          [
            't' =>
              [
                0 => 'application/vnd.rn-realmedia',
              ],
          ],
        'rmi' =>
          [
            't' =>
              [
                0 => 'audio/midi',
              ],
          ],
        'rmj' =>
          [
            't' =>
              [
                0 => 'application/vnd.rn-realmedia',
              ],
          ],
        'rmm' =>
          [
            't' =>
              [
                0 => 'application/vnd.rn-realmedia',
              ],
          ],
        'rmp' =>
          [
            't' =>
              [
                0 => 'audio/x-pn-realaudio-plugin',
              ],
          ],
        'rms' =>
          [
            't' =>
              [
                0 => 'application/vnd.jcp.javame.midlet-rms',
                1 => 'application/vnd.rn-realmedia',
              ],
          ],
        'rmvb' =>
          [
            't' =>
              [
                0 => 'application/vnd.rn-realmedia',
              ],
          ],
        'rmx' =>
          [
            't' =>
              [
                0 => 'application/vnd.rn-realmedia',
              ],
          ],
        'rnc' =>
          [
            't' =>
              [
                0 => 'application/relax-ng-compact-syntax',
              ],
          ],
        'rng' =>
          [
            't' =>
              [
                0 => 'application/xml',
              ],
          ],
        'roa' =>
          [
            't' =>
              [
                0 => 'application/rpki-roa',
              ],
          ],
        'roff' =>
          [
            't' =>
              [
                0 => 'text/troff',
              ],
          ],
        'ros' =>
          [
            't' =>
              [
                0 => 'text/x-common-lisp',
              ],
          ],
        'rp' =>
          [
            't' =>
              [
                0 => 'image/vnd.rn-realpix',
              ],
          ],
        'rp9' =>
          [
            't' =>
              [
                0 => 'application/vnd.cloanto.rp9',
              ],
          ],
        'rpm' =>
          [
            't' =>
              [
                0 => 'application/x-rpm',
              ],
          ],
        'rpss' =>
          [
            't' =>
              [
                0 => 'application/vnd.nokia.radio-presets',
              ],
          ],
        'rpst' =>
          [
            't' =>
              [
                0 => 'application/vnd.nokia.radio-preset',
              ],
          ],
        'rq' =>
          [
            't' =>
              [
                0 => 'application/sparql-query',
              ],
          ],
        'rs' =>
          [
            't' =>
              [
                0 => 'application/rls-services+xml',
                1 => 'text/rust',
              ],
          ],
        'rsd' =>
          [
            't' =>
              [
                0 => 'application/rsd+xml',
              ],
          ],
        'rss' =>
          [
            't' =>
              [
                0 => 'application/rss+xml',
              ],
          ],
        'rst' =>
          [
            't' =>
              [
                0 => 'text/x-rst',
              ],
          ],
        'rt' =>
          [
            't' =>
              [
                0 => 'text/vnd.rn-realtext',
              ],
          ],
        'rtf' =>
          [
            't' =>
              [
                0 => 'application/rtf',
              ],
          ],
        'rtx' =>
          [
            't' =>
              [
                0 => 'text/richtext',
              ],
          ],
        'rv' =>
          [
            't' =>
              [
                0 => 'video/vnd.rn-realvideo',
              ],
          ],
        'rvx' =>
          [
            't' =>
              [
                0 => 'video/vnd.rn-realvideo',
              ],
          ],
        'rw2' =>
          [
            't' =>
              [
                0 => 'image/x-panasonic-rw2',
              ],
          ],
        'rz' =>
          [
            't' =>
              [
                0 => 'application/x-rzip',
              ],
          ],
        's' =>
          [
            't' =>
              [
                0 => 'text/x-asm',
              ],
          ],
        's3m' =>
          [
            't' =>
              [
                0 => 'audio/s3m',
                1 => 'audio/x-s3m',
              ],
          ],
        'saf' =>
          [
            't' =>
              [
                0 => 'application/vnd.yamaha.smaf-audio',
              ],
          ],
        'sage' =>
          [
            't' =>
              [
                0 => 'text/x-sagemath',
              ],
          ],
        'sam' =>
          [
            't' =>
              [
                0 => 'application/x-amipro',
              ],
          ],
        'sami' =>
          [
            't' =>
              [
                0 => 'application/x-sami',
              ],
          ],
        'sap' =>
          [
            't' =>
              [
                0 => 'application/x-thomson-sap-image',
              ],
          ],
        'sass' =>
          [
            't' =>
              [
                0 => 'text/x-sass',
              ],
          ],
        'sav' =>
          [
            't' =>
              [
                0 => 'application/x-spss-sav',
              ],
          ],
        'sbml' =>
          [
            't' =>
              [
                0 => 'application/sbml+xml',
              ],
          ],
        'sc' =>
          [
            't' =>
              [
                0 => 'application/vnd.ibm.secure-container',
                1 => 'text/x-scala',
              ],
          ],
        'scala' =>
          [
            't' =>
              [
                0 => 'text/x-scala',
              ],
          ],
        'scd' =>
          [
            't' =>
              [
                0 => 'application/x-msschedule',
              ],
          ],
        'scm' =>
          [
            't' =>
              [
                0 => 'application/vnd.lotus-screencam',
                1 => 'text/x-scheme',
              ],
          ],
        'scn' =>
          [
            't' =>
              [
                0 => 'application/x-godot-scene',
              ],
          ],
        'scope' =>
          [
            't' =>
              [
                0 => 'text/x-systemd-unit',
              ],
          ],
        'scq' =>
          [
            't' =>
              [
                0 => 'application/scvp-cv-request',
              ],
          ],
        'scr' =>
          [
            't' =>
              [
                0 => 'application/x-msdownload',
                1 => 'application/x-ms-ne-executable',
                2 => 'application/vnd.microsoft.portable-executable',
              ],
          ],
        'scs' =>
          [
            't' =>
              [
                0 => 'application/scvp-cv-response',
              ],
          ],
        'scss' =>
          [
            't' =>
              [
                0 => 'text/x-scss',
              ],
          ],
        'scurl' =>
          [
            't' =>
              [
                0 => 'text/vnd.curl.scurl',
              ],
          ],
        'sda' =>
          [
            't' =>
              [
                0 => 'application/vnd.stardivision.draw',
                1 => 'application/x-stardraw',
              ],
          ],
        'sdc' =>
          [
            't' =>
              [
                0 => 'application/vnd.stardivision.calc',
                1 => 'application/x-starcalc',
              ],
          ],
        'sdd' =>
          [
            't' =>
              [
                0 => 'application/vnd.stardivision.impress',
                1 => 'application/x-starimpress',
              ],
          ],
        'sdkd' =>
          [
            't' =>
              [
                0 => 'application/vnd.solent.sdkm+xml',
              ],
          ],
        'sdkm' =>
          [
            't' =>
              [
                0 => 'application/vnd.solent.sdkm+xml',
              ],
          ],
        'sdm' =>
          [
            't' =>
              [
                0 => 'application/vnd.stardivision.mail',
              ],
          ],
        'sdp' =>
          [
            't' =>
              [
                0 => 'application/sdp',
                1 => 'application/vnd.stardivision.impress-packed',
              ],
          ],
        'sds' =>
          [
            't' =>
              [
                0 => 'application/x-starchart',
                1 => 'application/vnd.stardivision.chart',
              ],
          ],
        'sdw' =>
          [
            't' =>
              [
                0 => 'application/vnd.stardivision.writer',
                1 => 'application/x-starwriter',
              ],
          ],
        'see' =>
          [
            't' =>
              [
                0 => 'application/vnd.seemail',
              ],
          ],
        'seed' =>
          [
            't' =>
              [
                0 => 'application/vnd.fdsn.seed',
              ],
          ],
        'sema' =>
          [
            't' =>
              [
                0 => 'application/vnd.sema',
              ],
          ],
        'semd' =>
          [
            't' =>
              [
                0 => 'application/vnd.semd',
              ],
          ],
        'semf' =>
          [
            't' =>
              [
                0 => 'application/vnd.semf',
              ],
          ],
        'ser' =>
          [
            't' =>
              [
                0 => 'application/java-serialized-object',
              ],
          ],
        'service' =>
          [
            't' =>
              [
                0 => 'text/x-dbus-service',
                1 => 'text/x-systemd-unit',
              ],
          ],
        'setpay' =>
          [
            't' =>
              [
                0 => 'application/set-payment-initiation',
              ],
          ],
        'setreg' =>
          [
            't' =>
              [
                0 => 'application/set-registration-initiation',
              ],
          ],
        'sfc' =>
          [
            't' =>
              [
                0 => 'application/vnd.nintendo.snes.rom',
              ],
          ],
        'sfd-hdstx' =>
          [
            't' =>
              [
                0 => 'application/vnd.hydrostatix.sof-data',
              ],
          ],
        'sfs' =>
          [
            't' =>
              [
                0 => 'application/vnd.spotfire.sfs',
                1 => 'application/vnd.squashfs',
              ],
          ],
        'sfv' =>
          [
            't' =>
              [
                0 => 'text/x-sfv',
              ],
          ],
        'sg' =>
          [
            't' =>
              [
                0 => 'application/x-sg1000-rom',
              ],
          ],
        'sgb' =>
          [
            't' =>
              [
                0 => 'application/x-gameboy-rom',
              ],
          ],
        'sgd' =>
          [
            't' =>
              [
                0 => 'application/x-genesis-rom',
              ],
          ],
        'sgf' =>
          [
            't' =>
              [
                0 => 'application/x-go-sgf',
              ],
          ],
        'sgi' =>
          [
            't' =>
              [
                0 => 'image/sgi',
                1 => 'image/x-sgi',
              ],
          ],
        'sgl' =>
          [
            't' =>
              [
                0 => 'application/x-starwriter-global',
                1 => 'application/vnd.stardivision.writer-global',
              ],
          ],
        'sgm' =>
          [
            't' =>
              [
                0 => 'text/sgml',
              ],
          ],
        'sgml' =>
          [
            't' =>
              [
                0 => 'text/sgml',
              ],
          ],
        'sh' =>
          [
            't' =>
              [
                0 => 'application/x-sh',
                1 => 'application/x-shellscript',
              ],
          ],
        'shape' =>
          [
            't' =>
              [
                0 => 'application/x-dia-shape',
              ],
          ],
        'shar' =>
          [
            't' =>
              [
                0 => 'application/x-shar',
              ],
          ],
        'shf' =>
          [
            't' =>
              [
                0 => 'application/shf+xml',
              ],
          ],
        'shn' =>
          [
            't' =>
              [
                0 => 'application/x-shorten',
              ],
          ],
        'siag' =>
          [
            't' =>
              [
                0 => 'application/x-siag',
              ],
          ],
        'sid' =>
          [
            't' =>
              [
                0 => 'image/x-mrsid-image',
                1 => 'audio/prs.sid',
              ],
          ],
        'sieve' =>
          [
            't' =>
              [
                0 => 'application/sieve',
              ],
          ],
        'sig' =>
          [
            't' =>
              [
                0 => 'application/pgp-signature',
              ],
          ],
        'sik' =>
          [
            't' =>
              [
                0 => 'application/x-trash',
              ],
          ],
        'sil' =>
          [
            't' =>
              [
                0 => 'audio/silk',
              ],
          ],
        'silo' =>
          [
            't' =>
              [
                0 => 'model/mesh',
              ],
          ],
        'sis' =>
          [
            't' =>
              [
                0 => 'application/vnd.symbian.install',
              ],
          ],
        'sisx' =>
          [
            't' =>
              [
                0 => 'application/vnd.symbian.install',
                1 => 'x-epoc/x-sisx-app',
              ],
          ],
        'sit' =>
          [
            't' =>
              [
                0 => 'application/x-stuffit',
              ],
          ],
        'sitx' =>
          [
            't' =>
              [
                0 => 'application/x-stuffitx',
              ],
          ],
        'siv' =>
          [
            't' =>
              [
                0 => 'application/sieve',
              ],
          ],
        'sk' =>
          [
            't' =>
              [
                0 => 'image/x-skencil',
              ],
          ],
        'sk1' =>
          [
            't' =>
              [
                0 => 'image/x-skencil',
              ],
          ],
        'skd' =>
          [
            't' =>
              [
                0 => 'application/vnd.koan',
              ],
          ],
        'skm' =>
          [
            't' =>
              [
                0 => 'application/vnd.koan',
              ],
          ],
        'skp' =>
          [
            't' =>
              [
                0 => 'application/vnd.koan',
              ],
          ],
        'skr' =>
          [
            't' =>
              [
                0 => 'application/pgp-keys',
              ],
          ],
        'skt' =>
          [
            't' =>
              [
                0 => 'application/vnd.koan',
              ],
          ],
        'sldm' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-powerpoint.slide.macroenabled.12',
              ],
          ],
        'sldx' =>
          [
            't' =>
              [
                0 => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
              ],
          ],
        'slice' =>
          [
            't' =>
              [
                0 => 'text/x-systemd-unit',
              ],
          ],
        'slk' =>
          [
            't' =>
              [
                0 => 'application/x-sylk',
              ],
          ],
        'slt' =>
          [
            't' =>
              [
                0 => 'application/vnd.epson.salt',
              ],
          ],
        'sm' =>
          [
            't' =>
              [
                0 => 'application/vnd.stepmania.stepchart',
              ],
          ],
        'smaf' =>
          [
            't' =>
              [
                0 => 'application/vnd.smaf',
              ],
          ],
        'smc' =>
          [
            't' =>
              [
                0 => 'application/vnd.nintendo.snes.rom',
              ],
          ],
        'smd' =>
          [
            't' =>
              [
                0 => 'application/x-starmail',
                1 => 'application/x-genesis-rom',
              ],
          ],
        'smf' =>
          [
            't' =>
              [
                0 => 'application/vnd.stardivision.math',
                1 => 'application/x-starmath',
              ],
          ],
        'smi' =>
          [
            't' =>
              [
                0 => 'application/smil+xml',
                1 => 'application/x-sami',
              ],
          ],
        'smil' =>
          [
            't' =>
              [
                0 => 'application/smil+xml',
              ],
          ],
        'smk' =>
          [
            't' =>
              [
                0 => 'video/vnd.radgamettools.smacker',
              ],
          ],
        'sml' =>
          [
            't' =>
              [
                0 => 'application/smil+xml',
              ],
          ],
        'sms' =>
          [
            't' =>
              [
                0 => 'application/x-sms-rom',
              ],
          ],
        'smv' =>
          [
            't' =>
              [
                0 => 'video/x-smv',
              ],
          ],
        'smzip' =>
          [
            't' =>
              [
                0 => 'application/vnd.stepmania.package',
              ],
          ],
        'snap' =>
          [
            't' =>
              [
                0 => 'application/vnd.snap',
              ],
          ],
        'snd' =>
          [
            't' =>
              [
                0 => 'audio/basic',
              ],
          ],
        'snf' =>
          [
            't' =>
              [
                0 => 'application/x-font-snf',
              ],
          ],
        'so' =>
          [
            't' =>
              [
                0 => 'application/octet-stream',
                1 => 'application/x-sharedlib',
              ],
          ],
        'socket' =>
          [
            't' =>
              [
                0 => 'text/x-systemd-unit',
              ],
          ],
        'spc' =>
          [
            't' =>
              [
                0 => 'application/x-pkcs7-certificates',
              ],
          ],
        'spd' =>
          [
            't' =>
              [
                0 => 'application/x-font-speedo',
              ],
          ],
        'spec' =>
          [
            't' =>
              [
                0 => 'text/x-rpm-spec',
              ],
          ],
        'spf' =>
          [
            't' =>
              [
                0 => 'application/vnd.yamaha.smaf-phrase',
              ],
          ],
        'spl' =>
          [
            't' =>
              [
                0 => 'application/x-futuresplash',
                1 => 'application/vnd.adobe.flash.movie',
              ],
          ],
        'spm' =>
          [
            't' =>
              [
                0 => 'application/x-source-rpm',
              ],
          ],
        'spot' =>
          [
            't' =>
              [
                0 => 'text/vnd.in3d.spot',
              ],
          ],
        'spp' =>
          [
            't' =>
              [
                0 => 'application/scvp-vp-response',
              ],
          ],
        'spq' =>
          [
            't' =>
              [
                0 => 'application/scvp-vp-request',
              ],
          ],
        'spx' =>
          [
            't' =>
              [
                0 => 'audio/ogg',
                1 => 'application/x-apple-systemprofiler+xml',
                2 => 'audio/x-speex+ogg',
                3 => 'audio/x-speex',
              ],
          ],
        'sqfs' =>
          [
            't' =>
              [
                0 => 'application/vnd.squashfs',
              ],
          ],
        'sql' =>
          [
            't' =>
              [
                0 => 'application/x-sql',
                1 => 'application/sql',
              ],
          ],
        'sqlite2' =>
          [
            't' =>
              [
                0 => 'application/x-sqlite2',
              ],
          ],
        'sqlite3' =>
          [
            't' =>
              [
                0 => 'application/vnd.sqlite3',
              ],
          ],
        'sqsh' =>
          [
            't' =>
              [
                0 => 'application/vnd.squashfs',
              ],
          ],
        'squashfs' =>
          [
            't' =>
              [
                0 => 'application/vnd.squashfs',
              ],
          ],
        'sr2' =>
          [
            't' =>
              [
                0 => 'image/x-sony-sr2',
              ],
          ],
        'src' =>
          [
            't' =>
              [
                0 => 'application/x-wais-source',
              ],
          ],
        'src.rpm' =>
          [
            't' =>
              [
                0 => 'application/x-source-rpm',
              ],
          ],
        'srf' =>
          [
            't' =>
              [
                0 => 'image/x-sony-srf',
              ],
          ],
        'srt' =>
          [
            't' =>
              [
                0 => 'application/x-subrip',
              ],
          ],
        'sru' =>
          [
            't' =>
              [
                0 => 'application/sru+xml',
              ],
          ],
        'srx' =>
          [
            't' =>
              [
                0 => 'application/sparql-results+xml',
              ],
          ],
        'ss' =>
          [
            't' =>
              [
                0 => 'text/x-scheme',
              ],
          ],
        'ssa' =>
          [
            't' =>
              [
                0 => 'text/x-ssa',
              ],
          ],
        'ssdl' =>
          [
            't' =>
              [
                0 => 'application/ssdl+xml',
              ],
          ],
        'sse' =>
          [
            't' =>
              [
                0 => 'application/vnd.kodak-descriptor',
              ],
          ],
        'ssf' =>
          [
            't' =>
              [
                0 => 'application/vnd.epson.ssf',
              ],
          ],
        'ssml' =>
          [
            't' =>
              [
                0 => 'application/ssml+xml',
              ],
          ],
        'st' =>
          [
            't' =>
              [
                0 => 'application/vnd.sailingtracker.track',
              ],
          ],
        'stc' =>
          [
            't' =>
              [
                0 => 'application/vnd.sun.xml.calc.template',
              ],
          ],
        'std' =>
          [
            't' =>
              [
                0 => 'application/vnd.sun.xml.draw.template',
              ],
          ],
        'step' =>
          [
            't' =>
              [
                0 => 'model/step',
              ],
          ],
        'stf' =>
          [
            't' =>
              [
                0 => 'application/vnd.wt.stf',
              ],
          ],
        'sti' =>
          [
            't' =>
              [
                0 => 'application/vnd.sun.xml.impress.template',
              ],
          ],
        'stk' =>
          [
            't' =>
              [
                0 => 'application/hyperstudio',
              ],
          ],
        'stl' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-pki.stl',
                1 => 'model/stl',
              ],
          ],
        'stm' =>
          [
            't' =>
              [
                0 => 'audio/x-stm',
              ],
          ],
        'stp' =>
          [
            't' =>
              [
                0 => 'model/step',
              ],
          ],
        'str' =>
          [
            't' =>
              [
                0 => 'application/vnd.pg.format',
              ],
          ],
        'stw' =>
          [
            't' =>
              [
                0 => 'application/vnd.sun.xml.writer.template',
              ],
          ],
        'sty' =>
          [
            't' =>
              [
                0 => 'text/x-tex',
              ],
          ],
        'sub' =>
          [
            't' =>
              [
                0 => 'text/vnd.dvb.subtitle',
                1 => 'image/vnd.dvb.subtitle',
                2 => 'text/x-microdvd',
                3 => 'text/x-mpsub',
                4 => 'text/x-subviewer',
              ],
          ],
        'sun' =>
          [
            't' =>
              [
                0 => 'image/x-sun-raster',
              ],
          ],
        'sus' =>
          [
            't' =>
              [
                0 => 'application/vnd.sus-calendar',
              ],
          ],
        'susp' =>
          [
            't' =>
              [
                0 => 'application/vnd.sus-calendar',
              ],
          ],
        'sv' =>
          [
            't' =>
              [
                0 => 'text/x-svsrc',
              ],
          ],
        'sv4cpio' =>
          [
            't' =>
              [
                0 => 'application/x-sv4cpio',
              ],
          ],
        'sv4crc' =>
          [
            't' =>
              [
                0 => 'application/x-sv4crc',
              ],
          ],
        'svc' =>
          [
            't' =>
              [
                0 => 'application/vnd.dvb.service',
              ],
          ],
        'svd' =>
          [
            't' =>
              [
                0 => 'application/vnd.svd',
              ],
          ],
        'svg' =>
          [
            't' =>
              [
                0 => 'image/svg+xml',
              ],
          ],
        'svg.gz' =>
          [
            't' =>
              [
                0 => 'image/svg+xml-compressed',
              ],
          ],
        'svgz' =>
          [
            't' =>
              [
                0 => 'image/svg+xml',
                1 => 'image/svg+xml-compressed',
              ],
          ],
        'svh' =>
          [
            't' =>
              [
                0 => 'text/x-svhdr',
              ],
          ],
        'swa' =>
          [
            't' =>
              [
                0 => 'application/x-director',
              ],
          ],
        'swap' =>
          [
            't' =>
              [
                0 => 'text/x-systemd-unit',
              ],
          ],
        'swf' =>
          [
            't' =>
              [
                0 => 'application/vnd.adobe.flash.movie',
              ],
          ],
        'swi' =>
          [
            't' =>
              [
                0 => 'application/vnd.aristanetworks.swi',
              ],
          ],
        'swm' =>
          [
            't' =>
              [
                0 => 'application/x-ms-wim',
              ],
          ],
        'sxc' =>
          [
            't' =>
              [
                0 => 'application/vnd.sun.xml.calc',
              ],
          ],
        'sxd' =>
          [
            't' =>
              [
                0 => 'application/vnd.sun.xml.draw',
              ],
          ],
        'sxg' =>
          [
            't' =>
              [
                0 => 'application/vnd.sun.xml.writer.global',
              ],
          ],
        'sxi' =>
          [
            't' =>
              [
                0 => 'application/vnd.sun.xml.impress',
              ],
          ],
        'sxm' =>
          [
            't' =>
              [
                0 => 'application/vnd.sun.xml.math',
              ],
          ],
        'sxw' =>
          [
            't' =>
              [
                0 => 'application/vnd.sun.xml.writer',
              ],
          ],
        'sylk' =>
          [
            't' =>
              [
                0 => 'application/x-sylk',
              ],
          ],
        'sys' =>
          [
            't' =>
              [
                0 => 'application/vnd.microsoft.portable-executable',
              ],
          ],
        't' =>
          [
            't' =>
              [
                0 => 'text/troff',
                1 => 'application/x-perl',
              ],
          ],
        't2t' =>
          [
            't' =>
              [
                0 => 'text/x-txt2tags',
              ],
          ],
        't3' =>
          [
            't' =>
              [
                0 => 'application/x-t3vm-image',
              ],
          ],
        'taglet' =>
          [
            't' =>
              [
                0 => 'application/vnd.mynfc',
              ],
          ],
        'tak' =>
          [
            't' =>
              [
                0 => 'audio/x-tak',
              ],
          ],
        'tao' =>
          [
            't' =>
              [
                0 => 'application/vnd.tao.intent-module-archive',
              ],
          ],
        'tar' =>
          [
            't' =>
              [
                0 => 'application/x-tar',
              ],
          ],
        'tar.bz' =>
          [
            't' =>
              [
                0 => 'application/x-bzip1-compressed-tar',
              ],
          ],
        'tar.bz2' =>
          [
            't' =>
              [
                0 => 'application/x-bzip2-compressed-tar',
              ],
          ],
        'tar.bz3' =>
          [
            't' =>
              [
                0 => 'application/x-bzip3-compressed-tar',
              ],
          ],
        'tar.gz' =>
          [
            't' =>
              [
                0 => 'application/x-compressed-tar',
              ],
          ],
        'tar.lrz' =>
          [
            't' =>
              [
                0 => 'application/x-lrzip-compressed-tar',
              ],
          ],
        'tar.lz' =>
          [
            't' =>
              [
                0 => 'application/x-lzip-compressed-tar',
              ],
          ],
        'tar.lz4' =>
          [
            't' =>
              [
                0 => 'application/x-lz4-compressed-tar',
              ],
          ],
        'tar.lzma' =>
          [
            't' =>
              [
                0 => 'application/x-lzma-compressed-tar',
              ],
          ],
        'tar.lzo' =>
          [
            't' =>
              [
                0 => 'application/x-tzo',
              ],
          ],
        'tar.rz' =>
          [
            't' =>
              [
                0 => 'application/x-rzip-compressed-tar',
              ],
          ],
        'tar.xz' =>
          [
            't' =>
              [
                0 => 'application/x-xz-compressed-tar',
              ],
          ],
        'tar.z' =>
          [
            't' =>
              [
                0 => 'application/x-tarz',
              ],
          ],
        'tar.zst' =>
          [
            't' =>
              [
                0 => 'application/x-zstd-compressed-tar',
              ],
          ],
        'target' =>
          [
            't' =>
              [
                0 => 'text/x-systemd-unit',
              ],
          ],
        'taz' =>
          [
            't' =>
              [
                0 => 'application/x-tarz',
              ],
          ],
        'tb2' =>
          [
            't' =>
              [
                0 => 'application/x-bzip2-compressed-tar',
              ],
          ],
        'tbz' =>
          [
            't' =>
              [
                0 => 'application/x-bzip1-compressed-tar',
              ],
          ],
        'tbz2' =>
          [
            't' =>
              [
                0 => 'application/x-bzip2-compressed-tar',
              ],
          ],
        'tbz3' =>
          [
            't' =>
              [
                0 => 'application/x-bzip3-compressed-tar',
              ],
          ],
        'tcap' =>
          [
            't' =>
              [
                0 => 'application/vnd.3gpp2.tcap',
              ],
          ],
        'tcl' =>
          [
            't' =>
              [
                0 => 'application/x-tcl',
                1 => 'text/tcl',
              ],
          ],
        'teacher' =>
          [
            't' =>
              [
                0 => 'application/vnd.smart.teacher',
              ],
          ],
        'tei' =>
          [
            't' =>
              [
                0 => 'application/tei+xml',
              ],
          ],
        'teicorpus' =>
          [
            't' =>
              [
                0 => 'application/tei+xml',
              ],
          ],
        'tex' =>
          [
            't' =>
              [
                0 => 'text/x-tex',
              ],
          ],
        'texi' =>
          [
            't' =>
              [
                0 => 'application/x-texinfo',
                1 => 'text/x-texinfo',
              ],
          ],
        'texinfo' =>
          [
            't' =>
              [
                0 => 'application/x-texinfo',
                1 => 'text/x-texinfo',
              ],
          ],
        'text' =>
          [
            't' =>
              [
                0 => 'text/plain',
              ],
          ],
        'tfi' =>
          [
            't' =>
              [
                0 => 'application/thraud+xml',
              ],
          ],
        'tfm' =>
          [
            't' =>
              [
                0 => 'application/x-tex-tfm',
              ],
          ],
        'tga' =>
          [
            't' =>
              [
                0 => 'image/x-tga',
              ],
          ],
        'tgz' =>
          [
            't' =>
              [
                0 => 'application/x-compressed-tar',
              ],
          ],
        'theme' =>
          [
            't' =>
              [
                0 => 'application/x-theme',
              ],
          ],
        'themepack' =>
          [
            't' =>
              [
                0 => 'application/x-windows-themepack',
              ],
          ],
        'thmx' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-officetheme',
              ],
          ],
        'tif' =>
          [
            't' =>
              [
                0 => 'image/tiff',
              ],
          ],
        'tiff' =>
          [
            't' =>
              [
                0 => 'image/tiff',
              ],
          ],
        'timer' =>
          [
            't' =>
              [
                0 => 'text/x-systemd-unit',
              ],
          ],
        'tk' =>
          [
            't' =>
              [
                0 => 'text/tcl',
              ],
          ],
        'tlrz' =>
          [
            't' =>
              [
                0 => 'application/x-lrzip-compressed-tar',
              ],
          ],
        'tlz' =>
          [
            't' =>
              [
                0 => 'application/x-lzma-compressed-tar',
              ],
          ],
        'tmo' =>
          [
            't' =>
              [
                0 => 'application/vnd.tmobile-livetv',
              ],
          ],
        'tmx' =>
          [
            't' =>
              [
                0 => 'application/x-tiled-tmx',
              ],
          ],
        'tnef' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-tnef',
              ],
          ],
        'tnf' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-tnef',
              ],
          ],
        'toc' =>
          [
            't' =>
              [
                0 => 'application/x-cdrdao-toc',
              ],
          ],
        'toml' =>
          [
            't' =>
              [
                0 => 'application/toml',
              ],
          ],
        'torrent' =>
          [
            't' =>
              [
                0 => 'application/x-bittorrent',
              ],
          ],
        'tpic' =>
          [
            't' =>
              [
                0 => 'image/x-tga',
              ],
          ],
        'tpl' =>
          [
            't' =>
              [
                0 => 'application/vnd.groove-tool-template',
              ],
          ],
        'tpt' =>
          [
            't' =>
              [
                0 => 'application/vnd.trid.tpt',
              ],
          ],
        'tr' =>
          [
            't' =>
              [
                0 => 'text/troff',
              ],
          ],
        'tra' =>
          [
            't' =>
              [
                0 => 'application/vnd.trueapp',
              ],
          ],
        'tres' =>
          [
            't' =>
              [
                0 => 'application/x-godot-resource',
              ],
          ],
        'trig' =>
          [
            't' =>
              [
                0 => 'application/trig',
              ],
          ],
        'trm' =>
          [
            't' =>
              [
                0 => 'application/x-msterminal',
              ],
          ],
        'trz' =>
          [
            't' =>
              [
                0 => 'application/x-rzip-compressed-tar',
              ],
          ],
        'ts' =>
          [
            't' =>
              [
                0 => 'video/mp2t',
                1 => 'text/vnd.trolltech.linguist',
              ],
          ],
        'tscn' =>
          [
            't' =>
              [
                0 => 'application/x-godot-scene',
              ],
          ],
        'tsd' =>
          [
            't' =>
              [
                0 => 'application/timestamped-data',
              ],
          ],
        'tsv' =>
          [
            't' =>
              [
                0 => 'text/tab-separated-values',
              ],
          ],
        'tsx' =>
          [
            't' =>
              [
                0 => 'application/x-tiled-tsx',
              ],
          ],
        'tta' =>
          [
            't' =>
              [
                0 => 'audio/x-tta',
              ],
          ],
        'ttc' =>
          [
            't' =>
              [
                0 => 'font/collection',
              ],
          ],
        'ttf' =>
          [
            't' =>
              [
                0 => 'font/ttf',
              ],
          ],
        'ttl' =>
          [
            't' =>
              [
                0 => 'text/turtle',
              ],
          ],
        'ttx' =>
          [
            't' =>
              [
                0 => 'application/x-font-ttx',
              ],
          ],
        'twd' =>
          [
            't' =>
              [
                0 => 'application/vnd.simtech-mindmapper',
              ],
          ],
        'twds' =>
          [
            't' =>
              [
                0 => 'application/vnd.simtech-mindmapper',
              ],
          ],
        'twig' =>
          [
            't' =>
              [
                0 => 'text/x-twig',
              ],
          ],
        'txd' =>
          [
            't' =>
              [
                0 => 'application/vnd.genomatix.tuxedo',
              ],
          ],
        'txf' =>
          [
            't' =>
              [
                0 => 'application/vnd.mobius.txf',
              ],
          ],
        'txt' =>
          [
            't' =>
              [
                0 => 'text/plain',
              ],
          ],
        'txz' =>
          [
            't' =>
              [
                0 => 'application/x-xz-compressed-tar',
              ],
          ],
        'typ' =>
          [
            't' =>
              [
                0 => 'text/x-typst',
              ],
          ],
        'tzo' =>
          [
            't' =>
              [
                0 => 'application/x-tzo',
              ],
          ],
        'tzst' =>
          [
            't' =>
              [
                0 => 'application/x-zstd-compressed-tar',
              ],
          ],
        'u32' =>
          [
            't' =>
              [
                0 => 'application/x-authorware-bin',
              ],
          ],
        'udeb' =>
          [
            't' =>
              [
                0 => 'application/vnd.debian.binary-package',
              ],
          ],
        'ufd' =>
          [
            't' =>
              [
                0 => 'application/vnd.ufdl',
              ],
          ],
        'ufdl' =>
          [
            't' =>
              [
                0 => 'application/vnd.ufdl',
              ],
          ],
        'ufraw' =>
          [
            't' =>
              [
                0 => 'application/x-ufraw',
              ],
          ],
        'ui' =>
          [
            't' =>
              [
                0 => 'application/x-designer',
                1 => 'application/x-gtk-builder',
              ],
          ],
        'uil' =>
          [
            't' =>
              [
                0 => 'text/x-uil',
              ],
          ],
        'ult' =>
          [
            't' =>
              [
                0 => 'audio/x-mod',
              ],
          ],
        'ulx' =>
          [
            't' =>
              [
                0 => 'application/x-glulx',
              ],
          ],
        'umj' =>
          [
            't' =>
              [
                0 => 'application/vnd.umajin',
              ],
          ],
        'unf' =>
          [
            't' =>
              [
                0 => 'application/x-nes-rom',
              ],
          ],
        'uni' =>
          [
            't' =>
              [
                0 => 'audio/x-mod',
              ],
          ],
        'unif' =>
          [
            't' =>
              [
                0 => 'application/x-nes-rom',
              ],
          ],
        'unityweb' =>
          [
            't' =>
              [
                0 => 'application/vnd.unity',
              ],
          ],
        'uoml' =>
          [
            't' =>
              [
                0 => 'application/vnd.uoml+xml',
              ],
          ],
        'uri' =>
          [
            't' =>
              [
                0 => 'text/uri-list',
              ],
          ],
        'uris' =>
          [
            't' =>
              [
                0 => 'text/uri-list',
              ],
          ],
        'url' =>
          [
            't' =>
              [
                0 => 'application/x-mswinurl',
              ],
          ],
        'urls' =>
          [
            't' =>
              [
                0 => 'text/uri-list',
              ],
          ],
        'ustar' =>
          [
            't' =>
              [
                0 => 'application/x-ustar',
              ],
          ],
        'utz' =>
          [
            't' =>
              [
                0 => 'application/vnd.uiq.theme',
              ],
          ],
        'uu' =>
          [
            't' =>
              [
                0 => 'text/x-uuencode',
              ],
          ],
        'uue' =>
          [
            't' =>
              [
                0 => 'text/x-uuencode',
              ],
          ],
        'uva' =>
          [
            't' =>
              [
                0 => 'audio/vnd.dece.audio',
              ],
          ],
        'uvd' =>
          [
            't' =>
              [
                0 => 'application/vnd.dece.data',
              ],
          ],
        'uvf' =>
          [
            't' =>
              [
                0 => 'application/vnd.dece.data',
              ],
          ],
        'uvg' =>
          [
            't' =>
              [
                0 => 'image/vnd.dece.graphic',
              ],
          ],
        'uvh' =>
          [
            't' =>
              [
                0 => 'video/vnd.dece.hd',
              ],
          ],
        'uvi' =>
          [
            't' =>
              [
                0 => 'image/vnd.dece.graphic',
              ],
          ],
        'uvm' =>
          [
            't' =>
              [
                0 => 'video/vnd.dece.mobile',
              ],
          ],
        'uvp' =>
          [
            't' =>
              [
                0 => 'video/vnd.dece.pd',
              ],
          ],
        'uvs' =>
          [
            't' =>
              [
                0 => 'video/vnd.dece.sd',
              ],
          ],
        'uvt' =>
          [
            't' =>
              [
                0 => 'application/vnd.dece.ttml+xml',
              ],
          ],
        'uvu' =>
          [
            't' =>
              [
                0 => 'video/vnd.uvvu.mp4',
              ],
          ],
        'uvv' =>
          [
            't' =>
              [
                0 => 'video/vnd.dece.video',
              ],
          ],
        'uvva' =>
          [
            't' =>
              [
                0 => 'audio/vnd.dece.audio',
              ],
          ],
        'uvvd' =>
          [
            't' =>
              [
                0 => 'application/vnd.dece.data',
              ],
          ],
        'uvvf' =>
          [
            't' =>
              [
                0 => 'application/vnd.dece.data',
              ],
          ],
        'uvvg' =>
          [
            't' =>
              [
                0 => 'image/vnd.dece.graphic',
              ],
          ],
        'uvvh' =>
          [
            't' =>
              [
                0 => 'video/vnd.dece.hd',
              ],
          ],
        'uvvi' =>
          [
            't' =>
              [
                0 => 'image/vnd.dece.graphic',
              ],
          ],
        'uvvm' =>
          [
            't' =>
              [
                0 => 'video/vnd.dece.mobile',
              ],
          ],
        'uvvp' =>
          [
            't' =>
              [
                0 => 'video/vnd.dece.pd',
              ],
          ],
        'uvvs' =>
          [
            't' =>
              [
                0 => 'video/vnd.dece.sd',
              ],
          ],
        'uvvt' =>
          [
            't' =>
              [
                0 => 'application/vnd.dece.ttml+xml',
              ],
          ],
        'uvvu' =>
          [
            't' =>
              [
                0 => 'video/vnd.uvvu.mp4',
              ],
          ],
        'uvvv' =>
          [
            't' =>
              [
                0 => 'video/vnd.dece.video',
              ],
          ],
        'uvvx' =>
          [
            't' =>
              [
                0 => 'application/vnd.dece.unspecified',
              ],
          ],
        'uvvz' =>
          [
            't' =>
              [
                0 => 'application/vnd.dece.zip',
              ],
          ],
        'uvx' =>
          [
            't' =>
              [
                0 => 'application/vnd.dece.unspecified',
              ],
          ],
        'uvz' =>
          [
            't' =>
              [
                0 => 'application/vnd.dece.zip',
              ],
          ],
        'v' =>
          [
            't' =>
              [
                0 => 'text/x-verilog',
              ],
          ],
        'v64' =>
          [
            't' =>
              [
                0 => 'application/x-n64-rom',
              ],
          ],
        'vala' =>
          [
            't' =>
              [
                0 => 'text/x-vala',
              ],
          ],
        'vapi' =>
          [
            't' =>
              [
                0 => 'text/x-vala',
              ],
          ],
        'vb' =>
          [
            't' =>
              [
                0 => 'application/x-virtual-boy-rom',
                1 => 'text/x-vb',
              ],
          ],
        'vbe' =>
          [
            't' =>
              [
                0 => 'text/vbscript.encode',
              ],
          ],
        'vbs' =>
          [
            't' =>
              [
                0 => 'text/vbscript',
              ],
          ],
        'vcard' =>
          [
            't' =>
              [
                0 => 'text/vcard',
              ],
          ],
        'vcd' =>
          [
            't' =>
              [
                0 => 'application/x-cdlink',
              ],
          ],
        'vcf' =>
          [
            't' =>
              [
                0 => 'text/vcard',
              ],
          ],
        'vcg' =>
          [
            't' =>
              [
                0 => 'application/vnd.groove-vcard',
              ],
          ],
        'vcs' =>
          [
            't' =>
              [
                0 => 'text/calendar',
              ],
          ],
        'vct' =>
          [
            't' =>
              [
                0 => 'text/vcard',
              ],
          ],
        'vcx' =>
          [
            't' =>
              [
                0 => 'application/vnd.vcx',
              ],
          ],
        'vda' =>
          [
            't' =>
              [
                0 => 'image/x-tga',
              ],
          ],
        'vdi' =>
          [
            't' =>
              [
                0 => 'application/x-vdi-disk',
              ],
          ],
        'vhd' =>
          [
            't' =>
              [
                0 => 'text/x-vhdl',
                1 => 'application/x-vhd-disk',
              ],
          ],
        'vhdl' =>
          [
            't' =>
              [
                0 => 'text/x-vhdl',
              ],
          ],
        'vhdx' =>
          [
            't' =>
              [
                0 => 'application/x-vhdx-disk',
              ],
          ],
        'vis' =>
          [
            't' =>
              [
                0 => 'application/vnd.visionary',
              ],
          ],
        'viv' =>
          [
            't' =>
              [
                0 => 'video/vnd.vivo',
              ],
          ],
        'vivo' =>
          [
            't' =>
              [
                0 => 'video/vnd.vivo',
              ],
          ],
        'vlc' =>
          [
            't' =>
              [
                0 => 'audio/x-mpegurl',
              ],
          ],
        'vmdk' =>
          [
            't' =>
              [
                0 => 'application/x-vmdk-disk',
              ],
          ],
        'vob' =>
          [
            't' =>
              [
                0 => 'video/x-ms-vob',
                1 => 'video/mpeg',
              ],
          ],
        'voc' =>
          [
            't' =>
              [
                0 => 'audio/x-voc',
              ],
          ],
        'vor' =>
          [
            't' =>
              [
                0 => 'application/vnd.stardivision.writer',
                1 => 'application/x-starwriter',
              ],
          ],
        'vox' =>
          [
            't' =>
              [
                0 => 'application/x-authorware-bin',
              ],
          ],
        'vpc' =>
          [
            't' =>
              [
                0 => 'application/x-vhd-disk',
              ],
          ],
        'vrm' =>
          [
            't' =>
              [
                0 => 'model/vrml',
              ],
          ],
        'vrml' =>
          [
            't' =>
              [
                0 => 'model/vrml',
              ],
          ],
        'vsd' =>
          [
            't' =>
              [
                0 => 'application/vnd.visio',
              ],
          ],
        'vsdm' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-visio.drawing.macroenabled.main+xml',
              ],
          ],
        'vsdx' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-visio.drawing.main+xml',
              ],
          ],
        'vsf' =>
          [
            't' =>
              [
                0 => 'application/vnd.vsf',
              ],
          ],
        'vss' =>
          [
            't' =>
              [
                0 => 'application/vnd.visio',
              ],
          ],
        'vssm' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-visio.stencil.macroenabled.main+xml',
              ],
          ],
        'vssx' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-visio.stencil.main+xml',
              ],
          ],
        'vst' =>
          [
            't' =>
              [
                0 => 'application/vnd.visio',
                1 => 'image/x-tga',
              ],
          ],
        'vstm' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-visio.template.macroenabled.main+xml',
              ],
          ],
        'vstx' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-visio.template.main+xml',
              ],
          ],
        'vsw' =>
          [
            't' =>
              [
                0 => 'application/vnd.visio',
              ],
          ],
        'vtt' =>
          [
            't' =>
              [
                0 => 'text/vtt',
              ],
          ],
        'vtu' =>
          [
            't' =>
              [
                0 => 'model/vnd.vtu',
              ],
          ],
        'vxml' =>
          [
            't' =>
              [
                0 => 'application/voicexml+xml',
              ],
          ],
        'w3d' =>
          [
            't' =>
              [
                0 => 'application/x-director',
              ],
          ],
        'wad' =>
          [
            't' =>
              [
                0 => 'application/x-doom',
                1 => 'application/x-wii-wad',
                2 => 'application/x-doom-wad',
              ],
          ],
        'wasm' =>
          [
            't' =>
              [
                0 => 'application/wasm',
              ],
          ],
        'wav' =>
          [
            't' =>
              [
                0 => 'audio/vnd.wave',
              ],
          ],
        'wax' =>
          [
            't' =>
              [
                0 => 'audio/x-ms-wax',
                1 => 'audio/x-ms-asx',
              ],
          ],
        'wb1' =>
          [
            't' =>
              [
                0 => 'application/x-quattropro',
              ],
          ],
        'wb2' =>
          [
            't' =>
              [
                0 => 'application/x-quattropro',
              ],
          ],
        'wb3' =>
          [
            't' =>
              [
                0 => 'application/x-quattropro',
              ],
          ],
        'wbmp' =>
          [
            't' =>
              [
                0 => 'image/vnd.wap.wbmp',
              ],
          ],
        'wbs' =>
          [
            't' =>
              [
                0 => 'application/vnd.criticaltools.wbs+xml',
              ],
          ],
        'wbxml' =>
          [
            't' =>
              [
                0 => 'application/vnd.wap.wbxml',
              ],
          ],
        'wcm' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-works',
              ],
          ],
        'wdb' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-works',
              ],
          ],
        'wdp' =>
          [
            't' =>
              [
                0 => 'image/jxr',
              ],
          ],
        'weba' =>
          [
            't' =>
              [
                0 => 'audio/webm',
              ],
          ],
        'webm' =>
          [
            't' =>
              [
                0 => 'video/webm',
              ],
          ],
        'webp' =>
          [
            't' =>
              [
                0 => 'image/webp',
              ],
          ],
        'wg' =>
          [
            't' =>
              [
                0 => 'application/vnd.pmi.widget',
              ],
          ],
        'wgt' =>
          [
            't' =>
              [
                0 => 'application/widget',
              ],
          ],
        'wim' =>
          [
            't' =>
              [
                0 => 'application/x-ms-wim',
              ],
          ],
        'wk1' =>
          [
            't' =>
              [
                0 => 'application/vnd.lotus-1-2-3',
              ],
          ],
        'wk3' =>
          [
            't' =>
              [
                0 => 'application/vnd.lotus-1-2-3',
              ],
          ],
        'wk4' =>
          [
            't' =>
              [
                0 => 'application/vnd.lotus-1-2-3',
              ],
          ],
        'wkdownload' =>
          [
            't' =>
              [
                0 => 'application/x-partial-download',
              ],
          ],
        'wks' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-works',
                1 => 'application/vnd.lotus-1-2-3',
              ],
          ],
        'wm' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-asf',
              ],
          ],
        'wma' =>
          [
            't' =>
              [
                0 => 'audio/x-ms-wma',
              ],
          ],
        'wmd' =>
          [
            't' =>
              [
                0 => 'application/x-ms-wmd',
              ],
          ],
        'wmf' =>
          [
            't' =>
              [
                0 => 'image/wmf',
              ],
          ],
        'wml' =>
          [
            't' =>
              [
                0 => 'text/vnd.wap.wml',
              ],
          ],
        'wmlc' =>
          [
            't' =>
              [
                0 => 'application/vnd.wap.wmlc',
              ],
          ],
        'wmls' =>
          [
            't' =>
              [
                0 => 'text/vnd.wap.wmlscript',
              ],
          ],
        'wmlsc' =>
          [
            't' =>
              [
                0 => 'application/vnd.wap.wmlscriptc',
              ],
          ],
        'wmv' =>
          [
            't' =>
              [
                0 => 'video/x-ms-wmv',
              ],
          ],
        'wmx' =>
          [
            't' =>
              [
                0 => 'audio/x-ms-asx',
              ],
          ],
        'wmz' =>
          [
            't' =>
              [
                0 => 'application/x-ms-wmz',
              ],
          ],
        'woff' =>
          [
            't' =>
              [
                0 => 'font/woff',
              ],
          ],
        'woff2' =>
          [
            't' =>
              [
                0 => 'font/woff2',
              ],
          ],
        'wp' =>
          [
            't' =>
              [
                0 => 'application/vnd.wordperfect',
              ],
          ],
        'wp4' =>
          [
            't' =>
              [
                0 => 'application/vnd.wordperfect',
              ],
          ],
        'wp5' =>
          [
            't' =>
              [
                0 => 'application/vnd.wordperfect',
              ],
          ],
        'wp6' =>
          [
            't' =>
              [
                0 => 'application/vnd.wordperfect',
              ],
          ],
        'wpd' =>
          [
            't' =>
              [
                0 => 'application/vnd.wordperfect',
              ],
          ],
        'wpg' =>
          [
            't' =>
              [
                0 => 'application/x-wpg',
              ],
          ],
        'wpl' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-wpl',
              ],
          ],
        'wpp' =>
          [
            't' =>
              [
                0 => 'application/vnd.wordperfect',
              ],
          ],
        'wps' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-works',
              ],
          ],
        'wqd' =>
          [
            't' =>
              [
                0 => 'application/vnd.wqd',
              ],
          ],
        'wri' =>
          [
            't' =>
              [
                0 => 'application/x-mswrite',
              ],
          ],
        'wrl' =>
          [
            't' =>
              [
                0 => 'model/vrml',
              ],
          ],
        'ws' =>
          [
            't' =>
              [
                0 => 'application/x-wonderswan-rom',
              ],
          ],
        'wsc' =>
          [
            't' =>
              [
                0 => 'application/x-wonderswan-color-rom',
              ],
          ],
        'wsdl' =>
          [
            't' =>
              [
                0 => 'application/wsdl+xml',
              ],
          ],
        'wsgi' =>
          [
            't' =>
              [
                0 => 'text/x-python',
              ],
          ],
        'wspolicy' =>
          [
            't' =>
              [
                0 => 'application/wspolicy+xml',
              ],
          ],
        'wtb' =>
          [
            't' =>
              [
                0 => 'application/vnd.webturbo',
              ],
          ],
        'wv' =>
          [
            't' =>
              [
                0 => 'audio/x-wavpack',
              ],
          ],
        'wvc' =>
          [
            't' =>
              [
                0 => 'audio/x-wavpack-correction',
              ],
          ],
        'wvp' =>
          [
            't' =>
              [
                0 => 'audio/x-wavpack',
              ],
          ],
        'wvx' =>
          [
            't' =>
              [
                0 => 'audio/x-ms-asx',
              ],
          ],
        'wwf' =>
          [
            't' =>
              [
                0 => 'application/x-wwf',
              ],
          ],
        'x32' =>
          [
            't' =>
              [
                0 => 'application/x-authorware-bin',
              ],
          ],
        'x3d' =>
          [
            't' =>
              [
                0 => 'model/x3d+xml',
              ],
          ],
        'x3db' =>
          [
            't' =>
              [
                0 => 'model/x3d+binary',
              ],
          ],
        'x3dbz' =>
          [
            't' =>
              [
                0 => 'model/x3d+binary',
              ],
          ],
        'x3dv' =>
          [
            't' =>
              [
                0 => 'model/x3d+vrml',
              ],
          ],
        'x3dvz' =>
          [
            't' =>
              [
                0 => 'model/x3d+vrml',
              ],
          ],
        'x3dz' =>
          [
            't' =>
              [
                0 => 'model/x3d+xml',
              ],
          ],
        'x3f' =>
          [
            't' =>
              [
                0 => 'image/x-sigma-x3f',
              ],
          ],
        'xac' =>
          [
            't' =>
              [
                0 => 'application/x-gnucash',
              ],
          ],
        'xaml' =>
          [
            't' =>
              [
                0 => 'application/xaml+xml',
              ],
          ],
        'xap' =>
          [
            't' =>
              [
                0 => 'application/x-silverlight-app',
              ],
          ],
        'xar' =>
          [
            't' =>
              [
                0 => 'application/vnd.xara',
                1 => 'application/x-xar',
              ],
          ],
        'xbap' =>
          [
            't' =>
              [
                0 => 'application/x-ms-xbap',
              ],
          ],
        'xbd' =>
          [
            't' =>
              [
                0 => 'application/vnd.fujixerox.docuworks.binder',
              ],
          ],
        'xbel' =>
          [
            't' =>
              [
                0 => 'application/x-xbel',
              ],
          ],
        'xbl' =>
          [
            't' =>
              [
                0 => 'application/xml',
              ],
          ],
        'xbm' =>
          [
            't' =>
              [
                0 => 'image/x-xbitmap',
              ],
          ],
        'xcf' =>
          [
            't' =>
              [
                0 => 'image/x-xcf',
              ],
          ],
        'xcf.bz2' =>
          [
            't' =>
              [
                0 => 'image/x-compressed-xcf',
              ],
          ],
        'xcf.gz' =>
          [
            't' =>
              [
                0 => 'image/x-compressed-xcf',
              ],
          ],
        'xci' =>
          [
            't' =>
              [
                0 => 'application/x-nintendo-switch-xci',
              ],
          ],
        'xdf' =>
          [
            't' =>
              [
                0 => 'application/xcap-diff+xml',
              ],
          ],
        'xdgapp' =>
          [
            't' =>
              [
                0 => 'application/vnd.flatpak',
              ],
          ],
        'xdm' =>
          [
            't' =>
              [
                0 => 'application/vnd.syncml.dm+xml',
              ],
          ],
        'xdp' =>
          [
            't' =>
              [
                0 => 'application/vnd.adobe.xdp+xml',
              ],
          ],
        'xdssc' =>
          [
            't' =>
              [
                0 => 'application/dssc+xml',
              ],
          ],
        'xdw' =>
          [
            't' =>
              [
                0 => 'application/vnd.fujixerox.docuworks',
              ],
          ],
        'xenc' =>
          [
            't' =>
              [
                0 => 'application/xenc+xml',
              ],
          ],
        'xer' =>
          [
            't' =>
              [
                0 => 'application/patch-ops-error+xml',
              ],
          ],
        'xfdf' =>
          [
            't' =>
              [
                0 => 'application/vnd.adobe.xfdf',
              ],
          ],
        'xfdl' =>
          [
            't' =>
              [
                0 => 'application/vnd.xfdl',
              ],
          ],
        'xhe' =>
          [
            't' =>
              [
                0 => 'audio/usac',
              ],
          ],
        'xht' =>
          [
            't' =>
              [
                0 => 'application/xhtml+xml',
              ],
          ],
        'xhtml' =>
          [
            't' =>
              [
                0 => 'application/xhtml+xml',
              ],
          ],
        'xhvml' =>
          [
            't' =>
              [
                0 => 'application/xv+xml',
              ],
          ],
        'xi' =>
          [
            't' =>
              [
                0 => 'audio/x-xi',
              ],
          ],
        'xif' =>
          [
            't' =>
              [
                0 => 'image/vnd.xiff',
              ],
          ],
        'xla' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-excel',
              ],
          ],
        'xlam' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-excel.addin.macroenabled.12',
              ],
          ],
        'xlc' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-excel',
              ],
          ],
        'xld' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-excel',
              ],
          ],
        'xlf' =>
          [
            't' =>
              [
                0 => 'application/x-xliff+xml',
                1 => 'application/xliff+xml',
              ],
          ],
        'xliff' =>
          [
            't' =>
              [
                0 => 'application/xliff+xml',
              ],
          ],
        'xll' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-excel',
              ],
          ],
        'xlm' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-excel',
              ],
          ],
        'xlr' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-works',
              ],
          ],
        'xls' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-excel',
              ],
          ],
        'xlsb' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-excel.sheet.binary.macroenabled.12',
              ],
          ],
        'xlsm' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-excel.sheet.macroenabled.12',
              ],
          ],
        'xlsx' =>
          [
            't' =>
              [
                0 => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
              ],
          ],
        'xlt' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-excel',
              ],
          ],
        'xltm' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-excel.template.macroenabled.12',
              ],
          ],
        'xltx' =>
          [
            't' =>
              [
                0 => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
              ],
          ],
        'xlw' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-excel',
              ],
          ],
        'xm' =>
          [
            't' =>
              [
                0 => 'audio/xm',
                1 => 'audio/x-xm',
              ],
          ],
        'xmf' =>
          [
            't' =>
              [
                0 => 'audio/x-xmf',
              ],
          ],
        'xmi' =>
          [
            't' =>
              [
                0 => 'text/x-xmi',
              ],
          ],
        'xml' =>
          [
            't' =>
              [
                0 => 'application/xml',
              ],
          ],
        'xo' =>
          [
            't' =>
              [
                0 => 'application/vnd.olpc-sugar',
              ],
          ],
        'xop' =>
          [
            't' =>
              [
                0 => 'application/xop+xml',
              ],
          ],
        'xpi' =>
          [
            't' =>
              [
                0 => 'application/x-xpinstall',
              ],
          ],
        'xpl' =>
          [
            't' =>
              [
                0 => 'application/xproc+xml',
              ],
          ],
        'xpm' =>
          [
            't' =>
              [
                0 => 'image/x-xpixmap',
              ],
          ],
        'xpr' =>
          [
            't' =>
              [
                0 => 'application/vnd.is-xpr',
              ],
          ],
        'xps' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-xpsdocument',
              ],
          ],
        'xpw' =>
          [
            't' =>
              [
                0 => 'application/vnd.intercon.formnet',
              ],
          ],
        'xpx' =>
          [
            't' =>
              [
                0 => 'application/vnd.intercon.formnet',
              ],
          ],
        'xsd' =>
          [
            't' =>
              [
                0 => 'application/xml',
              ],
          ],
        'xsl' =>
          [
            't' =>
              [
                0 => 'application/xml',
                1 => 'application/xslt+xml',
              ],
          ],
        'xslfo' =>
          [
            't' =>
              [
                0 => 'text/x-xslfo',
              ],
          ],
        'xslt' =>
          [
            't' =>
              [
                0 => 'application/xslt+xml',
              ],
          ],
        'xsm' =>
          [
            't' =>
              [
                0 => 'application/vnd.syncml+xml',
              ],
          ],
        'xspf' =>
          [
            't' =>
              [
                0 => 'application/xspf+xml',
              ],
          ],
        'xul' =>
          [
            't' =>
              [
                0 => 'application/vnd.mozilla.xul+xml',
              ],
          ],
        'xvm' =>
          [
            't' =>
              [
                0 => 'application/xv+xml',
              ],
          ],
        'xvml' =>
          [
            't' =>
              [
                0 => 'application/xv+xml',
              ],
          ],
        'xwd' =>
          [
            't' =>
              [
                0 => 'image/x-xwindowdump',
              ],
          ],
        'xyz' =>
          [
            't' =>
              [
                0 => 'chemical/x-xyz',
              ],
          ],
        'xz' =>
          [
            't' =>
              [
                0 => 'application/x-xz',
              ],
          ],
        'yaml' =>
          [
            't' =>
              [
                0 => 'application/yaml',
              ],
          ],
        'yang' =>
          [
            't' =>
              [
                0 => 'application/yang',
              ],
          ],
        'yin' =>
          [
            't' =>
              [
                0 => 'application/yin+xml',
              ],
          ],
        'yml' =>
          [
            't' =>
              [
                0 => 'application/yaml',
              ],
          ],
        'yt' =>
          [
            't' =>
              [
                0 => 'video/vnd.youtube.yt',
              ],
          ],
        'z' =>
          [
            't' =>
              [
                0 => 'application/x-compress',
              ],
          ],
        'z1' =>
          [
            't' =>
              [
                0 => 'application/x-zmachine',
              ],
          ],
        'z2' =>
          [
            't' =>
              [
                0 => 'application/x-zmachine',
              ],
          ],
        'z3' =>
          [
            't' =>
              [
                0 => 'application/x-zmachine',
              ],
          ],
        'z4' =>
          [
            't' =>
              [
                0 => 'application/x-zmachine',
              ],
          ],
        'z5' =>
          [
            't' =>
              [
                0 => 'application/x-zmachine',
              ],
          ],
        'z6' =>
          [
            't' =>
              [
                0 => 'application/x-zmachine',
              ],
          ],
        'z64' =>
          [
            't' =>
              [
                0 => 'application/x-n64-rom',
              ],
          ],
        'z7' =>
          [
            't' =>
              [
                0 => 'application/x-zmachine',
              ],
          ],
        'z8' =>
          [
            't' =>
              [
                0 => 'application/x-zmachine',
              ],
          ],
        'zabw' =>
          [
            't' =>
              [
                0 => 'application/x-abiword',
              ],
          ],
        'zaz' =>
          [
            't' =>
              [
                0 => 'application/vnd.zzazz.deck+xml',
              ],
          ],
        'zim' =>
          [
            't' =>
              [
                0 => 'application/x-openzim',
              ],
          ],
        'zip' =>
          [
            't' =>
              [
                0 => 'application/zip',
              ],
          ],
        'zipx' =>
          [
            't' =>
              [
                0 => 'application/zip',
              ],
          ],
        'zir' =>
          [
            't' =>
              [
                0 => 'application/vnd.zul',
              ],
          ],
        'zirz' =>
          [
            't' =>
              [
                0 => 'application/vnd.zul',
              ],
          ],
        'zmm' =>
          [
            't' =>
              [
                0 => 'application/vnd.handheld-entertainment+xml',
              ],
          ],
        'zoo' =>
          [
            't' =>
              [
                0 => 'application/x-zoo',
              ],
          ],
        'zpaq' =>
          [
            't' =>
              [
                0 => 'application/x-zpaq',
              ],
          ],
        'zsav' =>
          [
            't' =>
              [
                0 => 'application/x-spss-sav',
              ],
          ],
        'zst' =>
          [
            't' =>
              [
                0 => 'application/zstd',
              ],
          ],
        'zz' =>
          [
            't' =>
              [
                0 => 'application/zlib',
              ],
          ],
      ],
    'a' =>
      [
        'application/acrobat' =>
          [
            't' =>
              [
                0 => 'application/pdf',
              ],
          ],
        'application/bat' =>
          [
            't' =>
              [
                0 => 'application/x-bat',
              ],
          ],
        'application/bzip2' =>
          [
            't' =>
              [
                0 => 'application/x-bzip2',
              ],
          ],
        'application/cdr' =>
          [
            't' =>
              [
                0 => 'application/vnd.corel-draw',
              ],
          ],
        'application/coreldraw' =>
          [
            't' =>
              [
                0 => 'application/vnd.corel-draw',
              ],
          ],
        'application/dbase' =>
          [
            't' =>
              [
                0 => 'application/vnd.dbf',
              ],
          ],
        'application/dbf' =>
          [
            't' =>
              [
                0 => 'application/vnd.dbf',
              ],
          ],
        'application/emf' =>
          [
            't' =>
              [
                0 => 'image/emf',
              ],
          ],
        'application/font-woff' =>
          [
            't' =>
              [
                0 => 'font/woff',
              ],
          ],
        'application/futuresplash' =>
          [
            't' =>
              [
                0 => 'application/vnd.adobe.flash.movie',
              ],
          ],
        'application/gpx' =>
          [
            't' =>
              [
                0 => 'application/gpx+xml',
              ],
          ],
        'application/ico' =>
          [
            't' =>
              [
                0 => 'image/vnd.microsoft.icon',
              ],
          ],
        'application/ics' =>
          [
            't' =>
              [
                0 => 'text/calendar',
              ],
          ],
        'application/java' =>
          [
            't' =>
              [
                0 => 'application/x-java',
              ],
          ],
        'application/java-byte-code' =>
          [
            't' =>
              [
                0 => 'application/x-java',
              ],
          ],
        'application/java-vm' =>
          [
            't' =>
              [
                0 => 'application/x-java',
              ],
          ],
        'application/javascript' =>
          [
            't' =>
              [
                0 => 'text/javascript',
              ],
          ],
        'application/lotus123' =>
          [
            't' =>
              [
                0 => 'application/vnd.lotus-1-2-3',
              ],
          ],
        'application/m3u' =>
          [
            't' =>
              [
                0 => 'audio/x-mpegurl',
              ],
          ],
        'application/mdb' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-access',
              ],
          ],
        'application/ms-tnef' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-tnef',
              ],
          ],
        'application/msaccess' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-access',
              ],
          ],
        'application/msexcel' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-excel',
              ],
          ],
        'application/mspowerpoint' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-powerpoint',
              ],
          ],
        'application/nappdf' =>
          [
            't' =>
              [
                0 => 'application/pdf',
              ],
          ],
        'application/pcap' =>
          [
            't' =>
              [
                0 => 'application/vnd.tcpdump.pcap',
              ],
          ],
        'application/pgp' =>
          [
            't' =>
              [
                0 => 'application/pgp-encrypted',
              ],
          ],
        'application/photoshop' =>
          [
            't' =>
              [
                0 => 'image/vnd.adobe.photoshop',
              ],
          ],
        'application/pls' =>
          [
            't' =>
              [
                0 => 'audio/x-scpls',
              ],
          ],
        'application/powerpoint' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-powerpoint',
              ],
          ],
        'application/prs.wavefront-obj' =>
          [
            't' =>
              [
                0 => 'model/obj',
              ],
          ],
        'application/smil' =>
          [
            't' =>
              [
                0 => 'application/smil+xml',
              ],
          ],
        'application/stuffit' =>
          [
            't' =>
              [
                0 => 'application/x-stuffit',
              ],
          ],
        'application/tga' =>
          [
            't' =>
              [
                0 => 'image/x-tga',
              ],
          ],
        'application/vnd.adobe.illustrator' =>
          [
            't' =>
              [
                0 => 'application/illustrator',
              ],
          ],
        'application/vnd.geo+json' =>
          [
            't' =>
              [
                0 => 'application/geo+json',
              ],
          ],
        'application/vnd.haansoft-hwp' =>
          [
            't' =>
              [
                0 => 'application/x-hwp',
              ],
          ],
        'application/vnd.haansoft-hwt' =>
          [
            't' =>
              [
                0 => 'application/x-hwt',
              ],
          ],
        'application/vnd.ms-3mfdocument' =>
          [
            't' =>
              [
                0 => 'model/3mf',
              ],
          ],
        'application/vnd.ms-word' =>
          [
            't' =>
              [
                0 => 'application/msword',
              ],
          ],
        'application/vnd.msaccess' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-access',
              ],
          ],
        'application/vnd.oasis.docbook+xml' =>
          [
            't' =>
              [
                0 => 'application/docbook+xml',
              ],
          ],
        'application/vnd.oasis.opendocument.database' =>
          [
            't' =>
              [
                0 => 'application/vnd.oasis.opendocument.base',
              ],
          ],
        'application/vnd.rn-realmedia-vbr' =>
          [
            't' =>
              [
                0 => 'application/vnd.rn-realmedia',
              ],
          ],
        'application/vnd.sdp' =>
          [
            't' =>
              [
                0 => 'application/sdp',
              ],
          ],
        'application/vnd.sun.xml.base' =>
          [
            't' =>
              [
                0 => 'application/vnd.oasis.opendocument.base',
              ],
          ],
        'application/vnd.truedoc' =>
          [
            't' =>
              [
                0 => 'application/font-tdpfr',
              ],
          ],
        'application/vnd.xdgapp' =>
          [
            't' =>
              [
                0 => 'application/vnd.flatpak',
              ],
          ],
        'application/vnd.youtube.yt' =>
          [
            't' =>
              [
                0 => 'video/vnd.youtube.yt',
              ],
          ],
        'application/wk1' =>
          [
            't' =>
              [
                0 => 'application/vnd.lotus-1-2-3',
              ],
          ],
        'application/wmf' =>
          [
            't' =>
              [
                0 => 'image/wmf',
              ],
          ],
        'application/wordperfect' =>
          [
            't' =>
              [
                0 => 'application/vnd.wordperfect',
              ],
          ],
        'application/wwf' =>
          [
            't' =>
              [
                0 => 'application/x-wwf',
              ],
          ],
        'application/x-123' =>
          [
            't' =>
              [
                0 => 'application/vnd.lotus-1-2-3',
              ],
          ],
        'application/x-annodex' =>
          [
            't' =>
              [
                0 => 'application/annodex',
              ],
          ],
        'application/x-bzip' =>
          [
            't' =>
              [
                0 => 'application/x-bzip2',
              ],
          ],
        'application/x-bzip-compressed-tar' =>
          [
            't' =>
              [
                0 => 'application/x-bzip2-compressed-tar',
              ],
          ],
        'application/x-cbr' =>
          [
            't' =>
              [
                0 => 'application/vnd.comicbook-rar',
              ],
          ],
        'application/x-cbz' =>
          [
            't' =>
              [
                0 => 'application/vnd.comicbook+zip',
              ],
          ],
        'application/x-cd-image' =>
          [
            't' =>
              [
                0 => 'application/vnd.efi.iso',
              ],
          ],
        'application/x-cdr' =>
          [
            't' =>
              [
                0 => 'application/vnd.corel-draw',
              ],
          ],
        'application/x-chess-pgn' =>
          [
            't' =>
              [
                0 => 'application/vnd.chess-pgn',
              ],
          ],
        'application/x-chm' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-htmlhelp',
              ],
          ],
        'application/x-coreldraw' =>
          [
            't' =>
              [
                0 => 'application/vnd.corel-draw',
              ],
          ],
        'application/x-dbase' =>
          [
            't' =>
              [
                0 => 'application/vnd.dbf',
              ],
          ],
        'application/x-dbf' =>
          [
            't' =>
              [
                0 => 'application/vnd.dbf',
              ],
          ],
        'application/x-deb' =>
          [
            't' =>
              [
                0 => 'application/vnd.debian.binary-package',
              ],
          ],
        'application/x-debian-package' =>
          [
            't' =>
              [
                0 => 'application/vnd.debian.binary-package',
              ],
          ],
        'application/x-docbook+xml' =>
          [
            't' =>
              [
                0 => 'application/docbook+xml',
              ],
          ],
        'application/x-emf' =>
          [
            't' =>
              [
                0 => 'image/emf',
              ],
          ],
        'application/x-fd-file' =>
          [
            't' =>
              [
                0 => 'application/x-raw-floppy-disk-image',
              ],
          ],
        'application/x-fictionbook' =>
          [
            't' =>
              [
                0 => 'application/x-fictionbook+xml',
              ],
          ],
        'application/x-flash-video' =>
          [
            't' =>
              [
                0 => 'video/x-flv',
              ],
          ],
        'application/x-font-otf' =>
          [
            't' =>
              [
                0 => 'font/otf',
              ],
          ],
        'application/x-font-ttf' =>
          [
            't' =>
              [
                0 => 'font/ttf',
              ],
          ],
        'application/x-frame' =>
          [
            't' =>
              [
                0 => 'application/vnd.framemaker',
              ],
          ],
        'application/x-gamecube-iso-image' =>
          [
            't' =>
              [
                0 => 'application/x-gamecube-rom',
              ],
          ],
        'application/x-gedcom' =>
          [
            't' =>
              [
                0 => 'text/vnd.familysearch.gedcom',
              ],
          ],
        'application/x-gerber' =>
          [
            't' =>
              [
                0 => 'application/vnd.gerber',
              ],
          ],
        'application/x-gettext' =>
          [
            't' =>
              [
                0 => 'text/x-gettext-translation',
              ],
          ],
        'application/x-gnome-app-info' =>
          [
            't' =>
              [
                0 => 'application/x-desktop',
              ],
          ],
        'application/x-gpx' =>
          [
            't' =>
              [
                0 => 'application/gpx+xml',
              ],
          ],
        'application/x-gpx+xml' =>
          [
            't' =>
              [
                0 => 'application/gpx+xml',
              ],
          ],
        'application/x-gtar' =>
          [
            't' =>
              [
                0 => 'application/x-tar',
              ],
          ],
        'application/x-gzip' =>
          [
            't' =>
              [
                0 => 'application/gzip',
              ],
          ],
        'application/x-hfe-file' =>
          [
            't' =>
              [
                0 => 'application/x-hfe-floppy-image',
              ],
          ],
        'application/x-iso9660-image' =>
          [
            't' =>
              [
                0 => 'application/vnd.efi.iso',
              ],
          ],
        'application/x-iwork-keynote-sffkey' =>
          [
            't' =>
              [
                0 => 'application/vnd.apple.keynote',
              ],
          ],
        'application/x-iwork-numbers-sffnumbers' =>
          [
            't' =>
              [
                0 => 'application/vnd.apple.numbers',
              ],
          ],
        'application/x-iwork-pages-sffpages' =>
          [
            't' =>
              [
                0 => 'application/vnd.apple.pages',
              ],
          ],
        'application/x-jar' =>
          [
            't' =>
              [
                0 => 'application/java-archive',
              ],
          ],
        'application/x-java-archive' =>
          [
            't' =>
              [
                0 => 'application/java-archive',
              ],
          ],
        'application/x-java-class' =>
          [
            't' =>
              [
                0 => 'application/x-java',
              ],
          ],
        'application/x-java-vm' =>
          [
            't' =>
              [
                0 => 'application/x-java',
              ],
          ],
        'application/x-javascript' =>
          [
            't' =>
              [
                0 => 'text/javascript',
              ],
          ],
        'application/x-kexiproject-sqlite' =>
          [
            't' =>
              [
                0 => 'application/x-kexiproject-sqlite3',
              ],
          ],
        'application/x-linguist' =>
          [
            't' =>
              [
                0 => 'text/vnd.trolltech.linguist',
              ],
          ],
        'application/x-lotus123' =>
          [
            't' =>
              [
                0 => 'application/vnd.lotus-1-2-3',
              ],
          ],
        'application/x-lzh-compressed' =>
          [
            't' =>
              [
                0 => 'application/x-lha',
              ],
          ],
        'application/x-mathematica' =>
          [
            't' =>
              [
                0 => 'application/mathematica',
              ],
          ],
        'application/x-mdb' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-access',
              ],
          ],
        'application/x-mobi8-ebook' =>
          [
            't' =>
              [
                0 => 'application/vnd.amazon.mobi8-ebook',
              ],
          ],
        'application/x-ms-asx' =>
          [
            't' =>
              [
                0 => 'audio/x-ms-asx',
              ],
          ],
        'application/x-ms-dos-executable' =>
          [
            't' =>
              [
                0 => 'application/x-msdownload',
              ],
          ],
        'application/x-msaccess' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-access',
              ],
          ],
        'application/x-msexcel' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-excel',
              ],
          ],
        'application/x-msmetafile' =>
          [
            't' =>
              [
                0 => 'image/wmf',
              ],
          ],
        'application/x-mspowerpoint' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-powerpoint',
              ],
          ],
        'application/x-msword' =>
          [
            't' =>
              [
                0 => 'application/msword',
              ],
          ],
        'application/x-nx-xci' =>
          [
            't' =>
              [
                0 => 'application/x-nintendo-switch-xci',
              ],
          ],
        'application/x-ogg' =>
          [
            't' =>
              [
                0 => 'application/ogg',
              ],
          ],
        'application/x-palm-database' =>
          [
            't' =>
              [
                0 => 'application/vnd.palm',
              ],
          ],
        'application/x-parquet' =>
          [
            't' =>
              [
                0 => 'application/vnd.apache.parquet',
              ],
          ],
        'application/x-pcap' =>
          [
            't' =>
              [
                0 => 'application/vnd.tcpdump.pcap',
              ],
          ],
        'application/x-pdf' =>
          [
            't' =>
              [
                0 => 'application/pdf',
              ],
          ],
        'application/x-photoshop' =>
          [
            't' =>
              [
                0 => 'image/vnd.adobe.photoshop',
              ],
          ],
        'application/x-pkcs12' =>
          [
            't' =>
              [
                0 => 'application/pkcs12',
              ],
          ],
        'application/x-quicktimeplayer' =>
          [
            't' =>
              [
                0 => 'application/x-quicktime-media-link',
              ],
          ],
        'application/x-rar' =>
          [
            't' =>
              [
                0 => 'application/vnd.rar',
              ],
          ],
        'application/x-rar-compressed' =>
          [
            't' =>
              [
                0 => 'application/vnd.rar',
              ],
          ],
        'application/x-raw-disk-image' =>
          [
            't' =>
              [
                0 => 'application/vnd.efi.img',
              ],
          ],
        'application/x-redhat-package-manager' =>
          [
            't' =>
              [
                0 => 'application/x-rpm',
              ],
          ],
        'application/x-reject' =>
          [
            't' =>
              [
                0 => 'text/x-reject',
              ],
          ],
        'application/x-rnc' =>
          [
            't' =>
              [
                0 => 'application/relax-ng-compact-syntax',
              ],
          ],
        'application/x-sap-file' =>
          [
            't' =>
              [
                0 => 'application/x-thomson-sap-image',
              ],
          ],
        'application/x-sdp' =>
          [
            't' =>
              [
                0 => 'application/sdp',
              ],
          ],
        'application/x-shockwave-flash' =>
          [
            't' =>
              [
                0 => 'application/vnd.adobe.flash.movie',
              ],
          ],
        'application/x-sit' =>
          [
            't' =>
              [
                0 => 'application/x-stuffit',
              ],
          ],
        'application/x-sitx' =>
          [
            't' =>
              [
                0 => 'application/x-stuffitx',
              ],
          ],
        'application/x-smaf' =>
          [
            't' =>
              [
                0 => 'application/vnd.smaf',
              ],
          ],
        'application/x-snes-rom' =>
          [
            't' =>
              [
                0 => 'application/vnd.nintendo.snes.rom',
              ],
          ],
        'application/x-spss-savefile' =>
          [
            't' =>
              [
                0 => 'application/x-spss-sav',
              ],
          ],
        'application/x-sqlite3' =>
          [
            't' =>
              [
                0 => 'application/vnd.sqlite3',
              ],
          ],
        'application/x-srt' =>
          [
            't' =>
              [
                0 => 'application/x-subrip',
              ],
          ],
        'application/x-targa' =>
          [
            't' =>
              [
                0 => 'image/x-tga',
              ],
          ],
        'application/x-tex' =>
          [
            't' =>
              [
                0 => 'text/x-tex',
              ],
          ],
        'application/x-tga' =>
          [
            't' =>
              [
                0 => 'image/x-tga',
              ],
          ],
        'application/x-trig' =>
          [
            't' =>
              [
                0 => 'application/trig',
              ],
          ],
        'application/x-troff' =>
          [
            't' =>
              [
                0 => 'text/troff',
              ],
          ],
        'application/x-virtualbox-ova' =>
          [
            't' =>
              [
                0 => 'application/ovf',
              ],
          ],
        'application/x-virtualbox-vdi' =>
          [
            't' =>
              [
                0 => 'application/x-vdi-disk',
              ],
          ],
        'application/x-virtualbox-vhd' =>
          [
            't' =>
              [
                0 => 'application/x-vhd-disk',
              ],
          ],
        'application/x-virtualbox-vhdx' =>
          [
            't' =>
              [
                0 => 'application/x-vhdx-disk',
              ],
          ],
        'application/x-virtualbox-vmdk' =>
          [
            't' =>
              [
                0 => 'application/x-vmdk-disk',
              ],
          ],
        'application/x-vnd.kde.kexi' =>
          [
            't' =>
              [
                0 => 'application/x-kexiproject-sqlite3',
              ],
          ],
        'application/x-wbfs' =>
          [
            't' =>
              [
                0 => 'application/x-wii-rom',
              ],
          ],
        'application/x-wia' =>
          [
            't' =>
              [
                0 => 'application/x-wii-rom',
              ],
          ],
        'application/x-wii-iso-image' =>
          [
            't' =>
              [
                0 => 'application/x-wii-rom',
              ],
          ],
        'application/x-win-lnk' =>
          [
            't' =>
              [
                0 => 'application/x-ms-shortcut',
              ],
          ],
        'application/x-wmf' =>
          [
            't' =>
              [
                0 => 'image/wmf',
              ],
          ],
        'application/x-wordperfect' =>
          [
            't' =>
              [
                0 => 'application/vnd.wordperfect',
              ],
          ],
        'application/x-xliff' =>
          [
            't' =>
              [
                0 => 'application/xliff+xml',
              ],
          ],
        'application/x-xspf+xml' =>
          [
            't' =>
              [
                0 => 'application/xspf+xml',
              ],
          ],
        'application/x-yaml' =>
          [
            't' =>
              [
                0 => 'application/yaml',
              ],
          ],
        'application/x-zip' =>
          [
            't' =>
              [
                0 => 'application/zip',
              ],
          ],
        'application/x-zip-compressed' =>
          [
            't' =>
              [
                0 => 'application/zip',
              ],
          ],
        'application/xps' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-xpsdocument',
              ],
          ],
        'audio/3gpp' =>
          [
            't' =>
              [
                0 => 'video/3gpp',
              ],
          ],
        'audio/3gpp-encrypted' =>
          [
            't' =>
              [
                0 => 'video/3gpp',
              ],
          ],
        'audio/3gpp2' =>
          [
            't' =>
              [
                0 => 'video/3gpp2',
              ],
          ],
        'audio/amr-encrypted' =>
          [
            't' =>
              [
                0 => 'audio/amr',
              ],
          ],
        'audio/amr-wb-encrypted' =>
          [
            't' =>
              [
                0 => 'audio/amr-wb',
              ],
          ],
        'audio/dff' =>
          [
            't' =>
              [
                0 => 'audio/x-dff',
              ],
          ],
        'audio/dsd' =>
          [
            't' =>
              [
                0 => 'audio/x-dsf',
              ],
          ],
        'audio/dsf' =>
          [
            't' =>
              [
                0 => 'audio/x-dsf',
              ],
          ],
        'audio/imelody' =>
          [
            't' =>
              [
                0 => 'text/x-imelody',
              ],
          ],
        'audio/m3u' =>
          [
            't' =>
              [
                0 => 'audio/x-mpegurl',
              ],
          ],
        'audio/m4a' =>
          [
            't' =>
              [
                0 => 'audio/mp4',
              ],
          ],
        'audio/mp3' =>
          [
            't' =>
              [
                0 => 'audio/mpeg',
              ],
          ],
        'audio/mpegurl' =>
          [
            't' =>
              [
                0 => 'audio/x-mpegurl',
              ],
          ],
        'audio/scpls' =>
          [
            't' =>
              [
                0 => 'audio/x-scpls',
              ],
          ],
        'audio/tta' =>
          [
            't' =>
              [
                0 => 'audio/x-tta',
              ],
          ],
        'audio/vnd.audible' =>
          [
            't' =>
              [
                0 => 'audio/x-pn-audibleaudio',
              ],
          ],
        'audio/vnd.m-realaudio' =>
          [
            't' =>
              [
                0 => 'audio/vnd.rn-realaudio',
              ],
          ],
        'audio/vnd.nokia.mobile-xmf' =>
          [
            't' =>
              [
                0 => 'audio/mobile-xmf',
              ],
          ],
        'audio/vorbis' =>
          [
            't' =>
              [
                0 => 'audio/x-vorbis+ogg',
              ],
          ],
        'audio/wav' =>
          [
            't' =>
              [
                0 => 'audio/vnd.wave',
              ],
          ],
        'audio/wma' =>
          [
            't' =>
              [
                0 => 'audio/x-ms-wma',
              ],
          ],
        'audio/x-aac' =>
          [
            't' =>
              [
                0 => 'audio/aac',
              ],
          ],
        'audio/x-aiffc' =>
          [
            't' =>
              [
                0 => 'audio/x-aifc',
              ],
          ],
        'audio/x-annodex' =>
          [
            't' =>
              [
                0 => 'audio/annodex',
              ],
          ],
        'audio/x-dsd' =>
          [
            't' =>
              [
                0 => 'audio/x-dsf',
              ],
          ],
        'audio/x-dts' =>
          [
            't' =>
              [
                0 => 'audio/vnd.dts',
              ],
          ],
        'audio/x-dtshd' =>
          [
            't' =>
              [
                0 => 'audio/vnd.dts.hd',
              ],
          ],
        'audio/x-flac' =>
          [
            't' =>
              [
                0 => 'audio/flac',
              ],
          ],
        'audio/x-imelody' =>
          [
            't' =>
              [
                0 => 'text/x-imelody',
              ],
          ],
        'audio/x-m3u' =>
          [
            't' =>
              [
                0 => 'audio/x-mpegurl',
              ],
          ],
        'audio/x-m4a' =>
          [
            't' =>
              [
                0 => 'audio/mp4',
              ],
          ],
        'audio/x-midi' =>
          [
            't' =>
              [
                0 => 'audio/midi',
              ],
          ],
        'audio/x-mp2' =>
          [
            't' =>
              [
                0 => 'audio/mp2',
              ],
          ],
        'audio/x-mp3' =>
          [
            't' =>
              [
                0 => 'audio/mpeg',
              ],
          ],
        'audio/x-mp3-playlist' =>
          [
            't' =>
              [
                0 => 'audio/x-mpegurl',
              ],
          ],
        'audio/x-mpeg' =>
          [
            't' =>
              [
                0 => 'audio/mpeg',
              ],
          ],
        'audio/x-mpg' =>
          [
            't' =>
              [
                0 => 'audio/mpeg',
              ],
          ],
        'audio/x-ogg' =>
          [
            't' =>
              [
                0 => 'audio/ogg',
              ],
          ],
        'audio/x-oggflac' =>
          [
            't' =>
              [
                0 => 'audio/x-flac+ogg',
              ],
          ],
        'audio/x-pn-realaudio' =>
          [
            't' =>
              [
                0 => 'audio/vnd.rn-realaudio',
              ],
          ],
        'audio/x-rn-3gpp-amr' =>
          [
            't' =>
              [
                0 => 'video/3gpp',
              ],
          ],
        'audio/x-rn-3gpp-amr-encrypted' =>
          [
            't' =>
              [
                0 => 'video/3gpp',
              ],
          ],
        'audio/x-rn-3gpp-amr-wb' =>
          [
            't' =>
              [
                0 => 'video/3gpp',
              ],
          ],
        'audio/x-rn-3gpp-amr-wb-encrypted' =>
          [
            't' =>
              [
                0 => 'video/3gpp',
              ],
          ],
        'audio/x-shorten' =>
          [
            't' =>
              [
                0 => 'application/x-shorten',
              ],
          ],
        'audio/x-vorbis' =>
          [
            't' =>
              [
                0 => 'audio/x-vorbis+ogg',
              ],
          ],
        'audio/x-wav' =>
          [
            't' =>
              [
                0 => 'audio/vnd.wave',
              ],
          ],
        'audio/xmf' =>
          [
            't' =>
              [
                0 => 'audio/x-xmf',
              ],
          ],
        'flv-application/octet-stream' =>
          [
            't' =>
              [
                0 => 'video/x-flv',
              ],
          ],
        'image/avif-sequence' =>
          [
            't' =>
              [
                0 => 'image/avif',
              ],
          ],
        'image/cdr' =>
          [
            't' =>
              [
                0 => 'application/vnd.corel-draw',
              ],
          ],
        'image/fax-g3' =>
          [
            't' =>
              [
                0 => 'image/g3fax',
              ],
          ],
        'image/fits' =>
          [
            't' =>
              [
                0 => 'application/fits',
              ],
          ],
        'image/heic' =>
          [
            't' =>
              [
                0 => 'image/heif',
              ],
          ],
        'image/heic-sequence' =>
          [
            't' =>
              [
                0 => 'image/heif',
              ],
          ],
        'image/heif-sequence' =>
          [
            't' =>
              [
                0 => 'image/heif',
              ],
          ],
        'image/ico' =>
          [
            't' =>
              [
                0 => 'image/vnd.microsoft.icon',
              ],
          ],
        'image/icon' =>
          [
            't' =>
              [
                0 => 'image/vnd.microsoft.icon',
              ],
          ],
        'image/jpeg2000' =>
          [
            't' =>
              [
                0 => 'image/jp2',
              ],
          ],
        'image/jpeg2000-image' =>
          [
            't' =>
              [
                0 => 'image/jp2',
              ],
          ],
        'image/pdf' =>
          [
            't' =>
              [
                0 => 'application/pdf',
              ],
          ],
        'image/photoshop' =>
          [
            't' =>
              [
                0 => 'image/vnd.adobe.photoshop',
              ],
          ],
        'image/pjpeg' =>
          [
            't' =>
              [
                0 => 'image/jpeg',
              ],
          ],
        'image/psd' =>
          [
            't' =>
              [
                0 => 'image/vnd.adobe.photoshop',
              ],
          ],
        'image/targa' =>
          [
            't' =>
              [
                0 => 'image/x-tga',
              ],
          ],
        'image/tga' =>
          [
            't' =>
              [
                0 => 'image/x-tga',
              ],
          ],
        'image/vnd.mozilla.apng' =>
          [
            't' =>
              [
                0 => 'image/apng',
              ],
          ],
        'image/vnd.ms-photo' =>
          [
            't' =>
              [
                0 => 'image/jxr',
              ],
          ],
        'image/x-bmp' =>
          [
            't' =>
              [
                0 => 'image/bmp',
              ],
          ],
        'image/x-cdr' =>
          [
            't' =>
              [
                0 => 'application/vnd.corel-draw',
              ],
          ],
        'image/x-djvu' =>
          [
            't' =>
              [
                0 => 'image/vnd.djvu',
              ],
          ],
        'image/x-emf' =>
          [
            't' =>
              [
                0 => 'image/emf',
              ],
          ],
        'image/x-fits' =>
          [
            't' =>
              [
                0 => 'application/fits',
              ],
          ],
        'image/x-fpx' =>
          [
            't' =>
              [
                0 => 'image/vnd.fpx',
              ],
          ],
        'image/x-icb' =>
          [
            't' =>
              [
                0 => 'image/x-tga',
              ],
          ],
        'image/x-ico' =>
          [
            't' =>
              [
                0 => 'image/vnd.microsoft.icon',
              ],
          ],
        'image/x-icon' =>
          [
            't' =>
              [
                0 => 'image/vnd.microsoft.icon',
              ],
          ],
        'image/x-iff' =>
          [
            't' =>
              [
                0 => 'image/x-ilbm',
              ],
          ],
        'image/x-jpeg2000-image' =>
          [
            't' =>
              [
                0 => 'image/jp2',
              ],
          ],
        'image/x-ms-bmp' =>
          [
            't' =>
              [
                0 => 'image/bmp',
              ],
          ],
        'image/x-panasonic-raw' =>
          [
            't' =>
              [
                0 => 'image/x-panasonic-rw',
              ],
          ],
        'image/x-panasonic-raw2' =>
          [
            't' =>
              [
                0 => 'image/x-panasonic-rw2',
              ],
          ],
        'image/x-pcx' =>
          [
            't' =>
              [
                0 => 'image/vnd.zbrush.pcx',
              ],
          ],
        'image/x-photoshop' =>
          [
            't' =>
              [
                0 => 'image/vnd.adobe.photoshop',
              ],
          ],
        'image/x-psd' =>
          [
            't' =>
              [
                0 => 'image/vnd.adobe.photoshop',
              ],
          ],
        'image/x-targa' =>
          [
            't' =>
              [
                0 => 'image/x-tga',
              ],
          ],
        'image/x-win-metafile' =>
          [
            't' =>
              [
                0 => 'image/wmf',
              ],
          ],
        'image/x-wmf' =>
          [
            't' =>
              [
                0 => 'image/wmf',
              ],
          ],
        'image/x-xpm' =>
          [
            't' =>
              [
                0 => 'image/x-xpixmap',
              ],
          ],
        'image/x.djvu' =>
          [
            't' =>
              [
                0 => 'image/vnd.djvu',
              ],
          ],
        'model/x.stl-ascii' =>
          [
            't' =>
              [
                0 => 'model/stl',
              ],
          ],
        'model/x.stl-binary' =>
          [
            't' =>
              [
                0 => 'model/stl',
              ],
          ],
        'text/crystal' =>
          [
            't' =>
              [
                0 => 'text/x-crystal',
              ],
          ],
        'text/directory' =>
          [
            't' =>
              [
                0 => 'text/vcard',
              ],
          ],
        'text/ecmascript' =>
          [
            't' =>
              [
                0 => 'application/ecmascript',
              ],
          ],
        'text/gedcom' =>
          [
            't' =>
              [
                0 => 'text/vnd.familysearch.gedcom',
              ],
          ],
        'text/google-video-pointer' =>
          [
            't' =>
              [
                0 => 'text/x-google-video-pointer',
              ],
          ],
        'text/ico' =>
          [
            't' =>
              [
                0 => 'image/vnd.microsoft.icon',
              ],
          ],
        'text/jscript' =>
          [
            't' =>
              [
                0 => 'text/javascript',
              ],
          ],
        'text/mathml' =>
          [
            't' =>
              [
                0 => 'application/mathml+xml',
              ],
          ],
        'text/rdf' =>
          [
            't' =>
              [
                0 => 'application/rdf+xml',
              ],
          ],
        'text/rss' =>
          [
            't' =>
              [
                0 => 'application/rss+xml',
              ],
          ],
        'text/rtf' =>
          [
            't' =>
              [
                0 => 'application/rtf',
              ],
          ],
        'text/spreadsheet' =>
          [
            't' =>
              [
                0 => 'application/x-sylk',
              ],
          ],
        'text/vbs' =>
          [
            't' =>
              [
                0 => 'text/vbscript',
              ],
          ],
        'text/vnd.qt.linguist' =>
          [
            't' =>
              [
                0 => 'text/vnd.trolltech.linguist',
              ],
          ],
        'text/x-c' =>
          [
            't' =>
              [
                0 => 'text/x-csrc',
              ],
          ],
        'text/x-comma-separated-values' =>
          [
            't' =>
              [
                0 => 'text/csv',
              ],
          ],
        'text/x-csv' =>
          [
            't' =>
              [
                0 => 'text/csv',
              ],
          ],
        'text/x-dart' =>
          [
            't' =>
              [
                0 => 'application/vnd.dart',
              ],
          ],
        'text/x-diff' =>
          [
            't' =>
              [
                0 => 'text/x-patch',
              ],
          ],
        'text/x-dtd' =>
          [
            't' =>
              [
                0 => 'application/xml-dtd',
              ],
          ],
        'text/x-fish' =>
          [
            't' =>
              [
                0 => 'application/x-fishscript',
              ],
          ],
        'text/x-lyx' =>
          [
            't' =>
              [
                0 => 'application/x-lyx',
              ],
          ],
        'text/x-markdown' =>
          [
            't' =>
              [
                0 => 'text/markdown',
              ],
          ],
        'text/x-nu' =>
          [
            't' =>
              [
                0 => 'application/x-nuscript',
              ],
          ],
        'text/x-octave' =>
          [
            't' =>
              [
                0 => 'text/x-matlab',
              ],
          ],
        'text/x-opml' =>
          [
            't' =>
              [
                0 => 'text/x-opml+xml',
              ],
          ],
        'text/x-perl' =>
          [
            't' =>
              [
                0 => 'application/x-perl',
              ],
          ],
        'text/x-po' =>
          [
            't' =>
              [
                0 => 'text/x-gettext-translation',
              ],
          ],
        'text/x-pot' =>
          [
            't' =>
              [
                0 => 'text/x-gettext-translation-template',
              ],
          ],
        'text/x-sh' =>
          [
            't' =>
              [
                0 => 'application/x-shellscript',
              ],
          ],
        'text/x-sql' =>
          [
            't' =>
              [
                0 => 'application/sql',
              ],
          ],
        'text/x-tcl' =>
          [
            't' =>
              [
                0 => 'text/tcl',
              ],
          ],
        'text/x-troff' =>
          [
            't' =>
              [
                0 => 'text/troff',
              ],
          ],
        'text/x-vcalendar' =>
          [
            't' =>
              [
                0 => 'text/calendar',
              ],
          ],
        'text/x-vcard' =>
          [
            't' =>
              [
                0 => 'text/vcard',
              ],
          ],
        'text/x-yaml' =>
          [
            't' =>
              [
                0 => 'application/yaml',
              ],
          ],
        'text/xml' =>
          [
            't' =>
              [
                0 => 'application/xml',
              ],
          ],
        'text/xml-external-parsed-entity' =>
          [
            't' =>
              [
                0 => 'application/xml-external-parsed-entity',
              ],
          ],
        'text/yaml' =>
          [
            't' =>
              [
                0 => 'application/yaml',
              ],
          ],
        'video/3gp' =>
          [
            't' =>
              [
                0 => 'video/3gpp',
              ],
          ],
        'video/3gpp-encrypted' =>
          [
            't' =>
              [
                0 => 'video/3gpp',
              ],
          ],
        'video/avi' =>
          [
            't' =>
              [
                0 => 'video/vnd.avi',
              ],
          ],
        'video/divx' =>
          [
            't' =>
              [
                0 => 'video/vnd.avi',
              ],
          ],
        'video/fli' =>
          [
            't' =>
              [
                0 => 'video/x-flic',
              ],
          ],
        'video/flv' =>
          [
            't' =>
              [
                0 => 'video/x-flv',
              ],
          ],
        'video/mp4v-es' =>
          [
            't' =>
              [
                0 => 'video/mp4',
              ],
          ],
        'video/mpeg-system' =>
          [
            't' =>
              [
                0 => 'video/mpeg',
              ],
          ],
        'video/msvideo' =>
          [
            't' =>
              [
                0 => 'video/vnd.avi',
              ],
          ],
        'video/vivo' =>
          [
            't' =>
              [
                0 => 'video/vnd.vivo',
              ],
          ],
        'video/vnd.divx' =>
          [
            't' =>
              [
                0 => 'video/vnd.avi',
              ],
          ],
        'video/x-annodex' =>
          [
            't' =>
              [
                0 => 'video/annodex',
              ],
          ],
        'video/x-avi' =>
          [
            't' =>
              [
                0 => 'video/vnd.avi',
              ],
          ],
        'video/x-fli' =>
          [
            't' =>
              [
                0 => 'video/x-flic',
              ],
          ],
        'video/x-m4v' =>
          [
            't' =>
              [
                0 => 'video/mp4',
              ],
          ],
        'video/x-mpeg' =>
          [
            't' =>
              [
                0 => 'video/mpeg',
              ],
          ],
        'video/x-mpeg-system' =>
          [
            't' =>
              [
                0 => 'video/mpeg',
              ],
          ],
        'video/x-mpeg2' =>
          [
            't' =>
              [
                0 => 'video/mpeg',
              ],
          ],
        'video/x-mpegurl' =>
          [
            't' =>
              [
                0 => 'video/vnd.mpegurl',
              ],
          ],
        'video/x-ms-asf' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-asf',
              ],
          ],
        'video/x-ms-asf-plugin' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-asf',
              ],
          ],
        'video/x-ms-wax' =>
          [
            't' =>
              [
                0 => 'audio/x-ms-asx',
              ],
          ],
        'video/x-ms-wm' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-asf',
              ],
          ],
        'video/x-ms-wmx' =>
          [
            't' =>
              [
                0 => 'audio/x-ms-asx',
              ],
          ],
        'video/x-ms-wvx' =>
          [
            't' =>
              [
                0 => 'audio/x-ms-asx',
              ],
          ],
        'video/x-msvideo' =>
          [
            't' =>
              [
                0 => 'video/vnd.avi',
              ],
          ],
        'video/x-ogg' =>
          [
            't' =>
              [
                0 => 'video/ogg',
              ],
          ],
        'video/x-ogm' =>
          [
            't' =>
              [
                0 => 'video/x-ogm+ogg',
              ],
          ],
        'video/x-real-video' =>
          [
            't' =>
              [
                0 => 'video/vnd.rn-realvideo',
              ],
          ],
        'video/x-theora' =>
          [
            't' =>
              [
                0 => 'video/x-theora+ogg',
              ],
          ],
        'zz-application/zz-winassoc-123' =>
          [
            't' =>
              [
                0 => 'application/vnd.lotus-1-2-3',
              ],
          ],
        'zz-application/zz-winassoc-cab' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-cab-compressed',
              ],
          ],
        'zz-application/zz-winassoc-cdr' =>
          [
            't' =>
              [
                0 => 'application/vnd.corel-draw',
              ],
          ],
        'zz-application/zz-winassoc-doc' =>
          [
            't' =>
              [
                0 => 'application/msword',
              ],
          ],
        'zz-application/zz-winassoc-hlp' =>
          [
            't' =>
              [
                0 => 'application/winhlp',
              ],
          ],
        'zz-application/zz-winassoc-mdb' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-access',
              ],
          ],
        'zz-application/zz-winassoc-uu' =>
          [
            't' =>
              [
                0 => 'text/x-uuencode',
              ],
          ],
        'zz-application/zz-winassoc-xls' =>
          [
            't' =>
              [
                0 => 'application/vnd.ms-excel',
              ],
          ],
      ],
  ];

  // phpcs:enable
}
