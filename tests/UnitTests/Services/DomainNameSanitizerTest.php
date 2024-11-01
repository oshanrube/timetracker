<?php

namespace App\Tests\UnitTests\Services;

use App\Services\DomainNameSanitizer;
use PHPUnit\Framework\TestCase;

class DomainNameSanitizerTest extends TestCase
{
    /**
     * @dataProvider domainNameProvider
     */
    public function testAcceptableDomainName(string $un_sanitized, string $sanitized): void
    {
        $subdomain = DomainNameSanitizer::sanitize($un_sanitized);
        $this->assertSame($sanitized, $subdomain);
    }

    public function domainNameProvider(): \Generator
    {
        yield 'full string' => ["This is a Test String with SpEcIaL Ch@rAcTers!", "this-is-a-test-string-with-special-ch-racters"];
        yield 'dots' => ["time.tracker", 'time-tracker'];
        yield 'keywords' => ["api", 'api'];
        yield 'Underscores are not allowed.' => ['my_domain', 'my-domain'];
        yield 'Domain names cannot start or end with a hyphen.' => ['[invalid URL removed]','invalid-url-removed'];
        yield 'Consecutive hyphens are not allowed.' => ['domain--name','domain-name'];
        yield 'Spaces are not allowed.' => ['domain name','domain-name'];
        yield 'This is a URL, not a domain name.' => ['http://domain','http-domain'];
        yield 'This is a https URL, not a domain name.' => ['https://domain','https-domain'];
        yield 'This is a subdomain, not a top-level domain.' => ['www.domain','www-domain'];
        yield 'Query strings are not part of the domain name.' => ['[invalid URL removed]','invalid-url-removed'];
        yield 'Fragment identifiers are not part of the domain name.' => ['domain#fragment','domain-fragment'];
        yield 'Invalid TLD.' => ['domain.c0m','domain-c0m'];
        yield 'Domain names cannot start with a number.' => ['[invalid URL removed]','invalid-url-removed'];
        yield 'Special characters like @ are not allowed.' => ['[email address removed]','email-address-removed'];
        yield 'Wildcards are not allowed.' => ['domain*','domain'];
        yield 'This is a relative path, not a domain name.' => ['../domain','domain'];
        yield 'This is a Windows-style path, not a domain name.' => ['domain\windows\system32','domain-windows-system32'];
        yield 'Port numbers are not part of the domain name.' => ['[invalid URL removed]','invalid-url-removed'];
        yield 'IP addresses and port numbers are not domain names.' => ['192.168.1.1:80','192-168-1-1-80'];
        yield 'Session IDs are not part of the domain name.' => ['domain;jsessionid=1234','domain-jsessionid=1234'];
        yield 'Incomplete URL.' => ['domain?','domain'];
        yield 'Invalid URL format.' => ['domain)&','domain-'];
    }
}