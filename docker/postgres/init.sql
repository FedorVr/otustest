CREATE DATABASE "skill-mentor-service";
CREATE DATABASE "skill-mentor-service-test";
CREATE USER "user" WITH ENCRYPTED PASSWORD 'password';
GRANT ALL PRIVILEGES ON DATABASE "skill-mentor-service" TO "user";
GRANT ALL PRIVILEGES ON DATABASE "skill-mentor-service_test" TO "user";