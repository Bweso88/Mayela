// Modèles miroirs des API Resources Laravel (UserResource, StudentProfileResource).
import '../../../core/theme/universe.dart';

/// Série (A, B, C, D…) ou spécialité.
class TrackRef {
  const TrackRef({required this.id, required this.code, required this.name});

  final int id;
  final String code;
  final String name;

  factory TrackRef.fromJson(Map<String, dynamic> json) =>
      TrackRef(id: json['id'] as int, code: json['code'] as String, name: json['name'] as String);
}

/// Classe (CE1 … Terminale) telle que renvoyée par l'API.
class GradeRef {
  const GradeRef({
    required this.id,
    required this.code,
    required this.name,
    required this.universe,
    this.contextName,
    this.tracks = const [],
  });

  final int id;
  final String code;
  final String name;
  final Universe universe;

  /// Cycle ou ordre d'enseignement (« Lycée », « Primaire »…), si connu.
  final String? contextName;
  final List<TrackRef> tracks;

  bool get requiresTrack => tracks.isNotEmpty;

  String get label => contextName == null ? name : '$name · $contextName';

  factory GradeRef.fromJson(Map<String, dynamic> json) {
    final cycle = _map(json['cycle']);
    final stage = _map(json['stage']);
    return GradeRef(
      id: json['id'] as int,
      code: json['code'] as String,
      name: json['name'] as String,
      universe: Universe.fromCode(json['universe'] as String?) ?? Universe.junior,
      contextName: (cycle?['name'] ?? stage?['name']) as String?,
      tracks: (json['tracks'] as List? ?? const []).map((t) => TrackRef.fromJson(_map(t)!)).toList(),
    );
  }
}

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
    this.grade,
    this.track,
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
  final GradeRef? grade;
  final TrackRef? track;

  /// Univers visuel : celui de la classe, sinon déduit de l'âge.
  Universe universeAt(DateTime now) => grade?.universe ?? Universe.fromAge(ageAt(now));

  /// « Première C », « CM2 », ou null si la classe n'est pas définie.
  String? get schoolingLabel => grade == null ? null : [grade!.name, if (track != null) track!.code].join(' ');

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
    grade: _parse(json['grade'], GradeRef.fromJson),
    track: _parse(json['track'], TrackRef.fromJson),
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
    studentProfile: _parse(json['student_profile'], StudentProfile.fromJson),
    children: (json['children'] as List? ?? const []).map((c) => StudentProfile.fromJson(_map(c)!)).toList(),
  );
}

/// Convertit un objet JSON en `Map<String, dynamic>` (null si ce n'est pas un objet).
Map<String, dynamic>? _map(Object? value) => value is Map ? Map<String, dynamic>.from(value) : null;

T? _parse<T>(Object? value, T Function(Map<String, dynamic>) fromJson) {
  final map = _map(value);
  return map == null ? null : fromJson(map);
}
