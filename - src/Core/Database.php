using System.Data;
using Microsoft.Data.SqlClient;

namespace AuthMicroservice.Core
{
    public class Database
    {
        private readonly string _connectionString = "Server=myServerAddress;Database=myDataBase;User Id=myUsername;Password=myPassword;";

        public IDbConnection CreateConnection()
        {
            return new SqlConnection(_connectionString);
        }
    }
}
