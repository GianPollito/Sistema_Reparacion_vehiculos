import pymysql

pymysql.version_info = (2, 2, 8, "final", 0)
pymysql.install_as_MySQLdb()

from django.db.backends.base.base import BaseDatabaseWrapper
BaseDatabaseWrapper.check_database_version_supported = lambda self: None

from django.db.backends.mysql.features import DatabaseFeatures
DatabaseFeatures.can_return_columns_from_insert = property(
    lambda self: self.connection.mysql_is_mariadb and self.connection.mysql_version >= (10, 5)
)
