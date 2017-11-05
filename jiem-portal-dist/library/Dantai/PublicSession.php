<?php
namespace Dantai;
class PublicSession extends PrivateSession
{
    const sysAdminRole = 1; //System Administrator
    const serviceManagerRole = 2; //Services Manager
    const orgSupervisorRole = 3; //Organization Supervisor
    const orgAdminRoleRole = 4; //Organization Administrator
    const orgUserRole = 5; //Organization User
    const viewerRole = 6; //Viewer

    public static function getRoleOfCurrentUser()
    {
        return self::getData('userIdentity')['roleId'];
    }

    public static function getOrganizationCode()
    {
        return self::getData('userIdentity')['organizationCode'];
    }

    public static function isSysAdminRole()
    {
        return self::sysAdminRole == self::getRoleOfCurrentUser();
    }

    public static function isServiceManagerRole()
    {
        return self::serviceManagerRole == self::getRoleOfCurrentUser();
    }

    public static function isOrgSupervisorRole()
    {
        return self::orgSupervisorRole == self::getRoleOfCurrentUser();
    }

    public static function isOrgAdminRoleRole()
    {
        return self::orgAdminRoleRole == self::getRoleOfCurrentUser();
    }

    public static function isOrgUserRoleRole()
    {
        return self::orgUserRole == self::getRoleOfCurrentUser();
    }

    public static function isViewerRole()
    {
        return self::viewerRole == self::getRoleOfCurrentUser();
    }

    public static function isDisableDownloadButtonRole()
    {
        return self::isServiceManagerRole() || self::isOrgSupervisorRole();
    }

    public static function isOrgAdminOrOrgUser()
    {
        return self::isOrgUserRoleRole() || self::isOrgAdminRoleRole();
    }

    public static function isSysAdminOrServiceManagerOrOrgSupervisor()
    {
        return self::isSysAdminRole() || self::isServiceManagerRole() || self::isOrgSupervisorRole();
    }

    public static function isRole1236()
    {
        return self::isSysAdminRole() || self::isServiceManagerRole() || self::isOrgSupervisorRole() || self::isViewerRole();
    }

    public static function isRole12()
    {
        return self::isSysAdminRole() || self::isServiceManagerRole();
    }

    public static function isHighSchool()
    {
        return in_array((int)self::getOrganizationCode(), array(1, 2, 3, 4, 5), true);
    }
}