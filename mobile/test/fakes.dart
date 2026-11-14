import 'package:mayela/core/api/api_exception.dart';
import 'package:mayela/features/auth/data/auth_repository.dart';
import 'package:mayela/core/theme/universe.dart';
import 'package:mayela/features/auth/data/models.dart';
import 'package:mayela/features/catalog/data/catalog_repository.dart';

final parentUser = AppUser(id: 'u-1', name: 'Grâce Nkounkou', roles: const ['parent'], children: [childProfile]);

final childProfile = StudentProfile(
  id: 's-1',
  firstName: 'Lucas',
  lastName: 'Mabiala',
  birthDate: DateTime(2018, 3, 1),
  loginCode: 'MY-7KQ4PX',
  level: 2,
  xp: 120,
);

final studentUser = AppUser(id: 'u-2', name: 'Lucas Mabiala', roles: const ['student'], studentProfile: childProfile);

/// Dépôt factice : simule le backend sans réseau.
class FakeAuthRepository implements AuthRepository {
  AppUser? sessionUser;
  ApiException? nextError;
  final calls = <String>[];

  Future<T> _answer<T>(String call, T value) async {
    calls.add(call);
    final error = nextError;
    if (error != null) {
      nextError = null;
      throw error;
    }
    return value;
  }

  /// Si [restoreError] est défini, la restauration échoue (les autres appels utilisent [nextError]).
  ApiException? restoreError;

  @override
  Future<AppUser?> restoreSession() async {
    calls.add('restore');
    if (restoreError != null) throw restoreError!;
    return sessionUser;
  }

  @override
  Future<AppUser> me() => _answer('me', sessionUser!);

  @override
  Future<AppUser> loginWithPassword({required String identifier, required String password}) =>
      _answer('login:$identifier', parentUser);

  @override
  Future<AppUser> loginWithStudentCode({required String code, required String pin}) =>
      _answer('student-login:$code:$pin', studentUser);

  @override
  Future<AppUser> registerParent({
    required String name,
    String? email,
    String? phone,
    required String password,
    required bool acceptTerms,
  }) => _answer('register:$name', parentUser);

  @override
  Future<StudentProfile> addChild({
    required String firstName,
    required String lastName,
    required DateTime birthDate,
    required String pin,
    required bool parentalConsent,
    String countryCode = 'CG',
    int? gradeId,
    int? trackId,
  }) => _answer('add-child:$firstName:${gradeId ?? '-'}:${trackId ?? '-'}', childProfile);

  @override
  Future<void> logout() => _answer('logout', null);
}

const seriesA = TrackRef(id: 1, code: 'A', name: 'Série A');
const seriesC = TrackRef(id: 3, code: 'C', name: 'Série C');

const gradeCm2 = GradeRef(id: 9, code: 'CM2', name: 'CM2', universe: Universe.junior, contextName: 'Primaire');
const gradePremiere = GradeRef(
  id: 15,
  code: '1ERE',
  name: 'Première',
  universe: Universe.lycee,
  contextName: 'Lycée',
  tracks: [seriesA, seriesC],
);

class FakeCatalogRepository implements CatalogRepository {
  final schoolingCalls = <String>[];

  @override
  Future<List<GradeRef>> grades(String countryCode) async => const [gradeCm2, gradePremiere];

  @override
  Future<StudentProfile> setChildSchooling(String studentId, {required int gradeId, int? trackId}) async {
    schoolingCalls.add('$studentId:$gradeId:${trackId ?? '-'}');
    return childProfile;
  }
}
