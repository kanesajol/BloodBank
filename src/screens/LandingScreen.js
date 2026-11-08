import {
  Image,
  ImageBackground,
  Pressable,
  SafeAreaView,
  ScrollView,
  StatusBar,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import SystemGuidelines from '../components/SystemGuidelines';

export default function LandingScreen({ onLogin, onSignUp }) {
  return (
    <ImageBackground
      source={require('../../assets/landing-background.png')}
      resizeMode="cover"
      style={styles.root}
    >
      <SafeAreaView style={styles.safeArea}>
        <StatusBar barStyle="dark-content" backgroundColor="#fff" />
        <SystemGuidelines withinSafeArea />
        <ScrollView
          contentContainerStyle={styles.content}
          showsVerticalScrollIndicator={false}
        >
          <View style={styles.hero}>
            <View style={styles.stickerFrame}>
              <Image
                source={require('../../assets/caraga-hospital-seal.png')}
                style={styles.sticker}
                resizeMode="cover"
                accessibilityLabel="Caraga Regional Hospital Surigao City seal"
              />
            </View>
            <View style={styles.copyCard}>
              <Text style={styles.welcome}>Welcome to</Text>
              <Text style={styles.title}>
                CARAGA REGIONAL HOSPITAL{' '}
                <Text style={styles.titleAccent}>BLOOD BANK</Text>
              </Text>
              <View style={styles.accentLine} />
              <Text style={styles.description}>
                We are committed to provide safe, sufficient, and quality blood
                to save lives and improve health in our community.
              </Text>
            </View>
          </View>

          <View style={styles.actions}>
            <Pressable
              accessibilityRole="button"
              style={[styles.button, styles.loginButton]}
              onPress={onLogin}
            >
              <Text style={styles.loginButtonText}>Staff Login</Text>
            </Pressable>
            <Pressable
              accessibilityRole="button"
              style={[styles.button, styles.signUpButton]}
              onPress={onSignUp}
            >
              <Text style={styles.signUpButtonText}>Sign Up</Text>
            </Pressable>
          </View>
        </ScrollView>
      </SafeAreaView>
    </ImageBackground>
  );
}

const styles = StyleSheet.create({
  root: {
    flex: 1,
    borderTopWidth: 5,
    borderTopColor: '#990000',
  },
  safeArea: {
    flex: 1,
    backgroundColor: 'transparent',
  },
  content: {
    flexGrow: 1,
    justifyContent: 'center',
    paddingHorizontal: 26,
    paddingVertical: 24,
  },
  hero: {
    alignItems: 'center',
  },
  stickerFrame: {
    width: 172,
    height: 172,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 26,
    overflow: 'hidden',
    borderRadius: 86,
    borderWidth: 4,
    borderColor: '#171717',
    backgroundColor: '#ffff00',
    elevation: 8,
  },
  sticker: {
    width: '100%',
    height: '100%',
  },
  copyCard: {
    width: '100%',
    alignItems: 'center',
    paddingHorizontal: 18,
    paddingVertical: 20,
    borderRadius: 20,
    borderWidth: 1,
    borderColor: 'rgba(153, 0, 0, 0.16)',
    backgroundColor: 'rgba(255, 255, 255, 0.94)',
    shadowColor: '#171717',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.12,
    shadowRadius: 12,
    elevation: 4,
  },
  welcome: {
    marginBottom: 10,
    color: '#990000',
    fontSize: 18,
    fontWeight: '800',
    textAlign: 'center',
  },
  title: {
    color: '#171717',
    fontSize: 25,
    fontWeight: '900',
    letterSpacing: 0.4,
    lineHeight: 34,
    textAlign: 'center',
  },
  titleAccent: {
    color: '#990000',
  },
  accentLine: {
    width: 58,
    height: 4,
    marginTop: 20,
    marginBottom: 18,
    borderRadius: 2,
    backgroundColor: '#990000',
  },
  description: {
    maxWidth: 340,
    color: '#4b4545',
    fontSize: 16,
    lineHeight: 25,
    textAlign: 'center',
  },
  actions: {
    flexDirection: 'row',
    gap: 12,
    marginTop: 36,
  },
  button: {
    minHeight: 52,
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 14,
    borderWidth: 2,
  },
  loginButton: {
    borderColor: '#990000',
    backgroundColor: '#990000',
  },
  loginButtonText: {
    color: '#fff',
    fontSize: 15,
    fontWeight: '700',
  },
  signUpButton: {
    borderColor: '#990000',
    backgroundColor: '#fff',
  },
  signUpButtonText: {
    color: '#990000',
    fontSize: 15,
    fontWeight: '700',
  },
});
