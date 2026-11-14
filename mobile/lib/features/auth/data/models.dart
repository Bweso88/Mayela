/// Modèles miroirs des API Resources Laravel (UserResource, StudentProfileResource).
class StudentProfile {
  const StudentProfile({
    required this.id,
    required this.firstName,
    required this.lastName,
    required this.birthDate,
    required this.loginCode,
    required this.level,
    required this.xp,
    this.countryCode,
    this.schoolName,
  });

  final String id;
  final String firstName;
  final String lastName;
  final DateTime birthDate;
  final String loginCode;
  final int level;
  final int xp;
  final String? countryCode;
  final String? schoolName;

  int ageAt(DateTime now) {
    var age = now.year - birthDate.year;
    if (now.month < birthDate.month || (now.month == birthDate.month && now.day < birthDate.day)) age--;
    return age;
  }

  factory StudentProfile.fromJson(Map<String, dynamic> json) => StudentProfile(
    id: json['id'] as String,
    firstName: json['first_name'] as String,
    lastName: json['last_name'] as String,
    birthDate: DateTime.parse(json['birth_date'] as String),
    loginCode: json['login_code'] as String,
    level: (json['level'] as num?)?.toInt() ?? 1,
    xp: (json['xp'] as num?)?.toInt() ?? 0,
    countryCode: json['country_code'] as String?,
    schoolName: json['school_name'] as String?,
  );
}

enum AppRole { parent, student }

class AppUser {
  const AppUser({
    required this.id,
    required this.name,
    required this.roles,
    this.email,
    this.phone,
    this.studentProfile,
    this.children = const [],
  });

  final String id;
  final String name;
  final String? email;
  final String? phone;
  final List<String> roles;
  final StudentProfile? studentProfile;
  final List<StudentProfile> children;

  AppRole get role => roles.contains('parent') ? AppRole.parent : AppRole.student;

  factory AppUser.fromJson(Map<String, dynamic> json) => AppUser(
    id: json['id'] as String,
    name: json['name'] as String,
    email: json['email'] as String?,
    phone: json['phone'] as String?,
    roles: (json['roles'] as List? ?? const []).cast<String>(),
    studentProfile: json['student_profile'] is Map<String, dynamic>
        ? StudentProfile.fromJson(json['student_profile'] as Map<String, dynamic>)
        : null,
    children: (json['children'] as List? ?? const [])
        .map((c) => StudentProfile.fromJson(c as Map<String, dynamic>))
        .toList(),
  );
}
